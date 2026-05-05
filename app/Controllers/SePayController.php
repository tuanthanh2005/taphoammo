<?php
// app/Controllers/SePayController.php

class SePayController extends Controller {
    public function handleWebhook() {
        // Ghi log chi tiết ra file để debug trên server
        $logFile = __DIR__ . '/../../storage/logs/sepay_webhook.log';
        $rawInput = file_get_contents('php://input');
        $headers = getallheaders();
        $logEntry = "[" . date('Y-m-d H:i:s') . "] IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
        $logEntry .= "HEADERS: " . json_encode($headers) . "\n";
        $logEntry .= "BODY: " . $rawInput . "\n";
        $logEntry .= "------------------------------------------\n";
        
        // Tạo thư mục nếu chưa có
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }
        file_put_contents($logFile, $logEntry, FILE_APPEND);

        // Lấy dữ liệu từ webhook
        $data = json_decode($rawInput, true);

        if (!$data) {
            Logger::error('SePay Webhook: Invalid JSON input');
            $this->json(['success' => false, 'message' => 'Invalid JSON'], 400);
            return;
        }

        // Kiểm tra Token bảo mật (nếu có cấu hình)
        $db = Database::getInstance();
        $webhookToken = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'sepay_webhook_token'")['value'] ?? '';
        
        $headers = getallheaders();
        $receivedToken = '';
        
        // 1. Thử lấy từ Header Authorization (SePay thường dùng)
        if (isset($headers['Authorization'])) {
            $receivedToken = str_replace(['Bearer ', 'Apikey '], '', $headers['Authorization']);
        } 
        // 2. Thử lấy từ Header x-api-key hoặc tương đương
        elseif (isset($headers['x-api-key'])) {
            $receivedToken = $headers['x-api-key'];
        }
        // 3. Nếu SePay gửi trong body (một số trường hợp)
        elseif (isset($data['token'])) {
            $receivedToken = $data['token'];
        }

        if ($webhookToken !== '' && $receivedToken !== $webhookToken) {
            file_put_contents($logFile, "[AUTH_FAILED] Received: $receivedToken, Expected: $webhookToken\n", FILE_APPEND);
            Logger::error('SePay Webhook: Invalid token');
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        // Xử lý giao dịch
        $content = $data['content'] ?? $data['description'] ?? '';
        $amount = (float)($data['transferAmount'] ?? $data['amount_in'] ?? 0);
        $transactionId = $data['id'] ?? $data['transaction_id'] ?? '';

        if ($amount <= 0) {
            $this->json(['success' => true, 'message' => 'Ignored zero amount']);
            return;
        }

        // Tìm yêu cầu nạp tiền
        $depositRequestModel = new DepositRequest();
        $deposit = null;

        // 1. Thử tìm chính xác theo nội dung nhận được
        $deposit = $db->fetchOne("SELECT * FROM deposit_requests WHERE (transfer_code = ? OR REPLACE(transfer_code, '_', '') = ?) AND status = 'pending'", [$content, str_replace('_', '', $content)]);

        if (!$deposit) {
            file_put_contents($logFile, "[DEBUG] No direct match for content: $content. Trying regex...\n", FILE_APPEND);
            // 2. Dùng Regex để bóc tách mã nạp tiền (chấp nhận mất dấu gạch dưới)
            if (preg_match('/(NAPUSER\s*\d+|NAPSELLER\s*\d+|NAP\d+_[A-Z0-9]+|NAP[A-Z0-9]+)/i', $content, $matches)) {
                $foundCode = strtoupper(trim($matches[0]));
                $codeWithoutUnderscore = str_replace('_', '', $foundCode);
                file_put_contents($logFile, "[DEBUG] Regex extracted: $foundCode (Normalized: $codeWithoutUnderscore)\n", FILE_APPEND);
                
                $deposit = $db->fetchOne(
                    "SELECT * FROM deposit_requests WHERE (transfer_code = ? OR REPLACE(transfer_code, '_', '') = ?) AND status = 'pending' ORDER BY id DESC LIMIT 1", 
                    [$foundCode, $codeWithoutUnderscore]
                );
            } else {
                file_put_contents($logFile, "[DEBUG] Regex could not find any NAP code in content.\n", FILE_APPEND);
            }
        }

        // 3. Fallback: Tìm theo User ID bóc tách từ mã
        if (!$deposit && isset($foundCode)) {
            if (preg_match('/NAP(?:USER|SELLER)?\s*0*(\d+)/i', $foundCode, $idMatches)) {
                $userId = $idMatches[1];
                file_put_contents($logFile, "[DEBUG] Fallback: Searching latest pending request for User ID: $userId\n", FILE_APPEND);
                $deposit = $db->fetchOne(
                    "SELECT * FROM deposit_requests WHERE user_id = ? AND status = 'pending' ORDER BY id DESC LIMIT 1",
                    [$userId]
                );
            }
        }

        if ($deposit) {
            file_put_contents($logFile, "[MATCHED] Successfully matched to Deposit Request ID: " . $deposit['id'] . "\n", FILE_APPEND);
            
            // Thực hiện cộng tiền
            try {
                $walletService = new WalletService();
                $walletService->addMoney(
                    $deposit['user_id'],
                    $amount,
                    'deposit',
                    'deposit_request',
                    $deposit['id'],
                    "Nạp tiền tự động qua SePay (GD: $transactionId)"
                );
                file_put_contents($logFile, "[SUCCESS] Money added to User ID: " . $deposit['user_id'] . "\n", FILE_APPEND);

                // Cập nhật trạng thái yêu cầu nạp tiền
                $depositRequestModel->update($deposit['id'], [
                    'status' => 'approved',
                    'admin_note' => "Duyệt tự động qua SePay. GD: $transactionId",
                    'processed_at' => date('Y-m-d H:i:s')
                ]);
                file_put_contents($logFile, "[SUCCESS] Deposit request status updated to approved.\n", FILE_APPEND);

                Logger::activity("SePay: Automatically approved deposit #{$deposit['id']} for User #{$deposit['user_id']}");
                $this->json(['success' => true, 'message' => 'Processed successfully']);
            } catch (Exception $ex) {
                file_put_contents($logFile, "[ERROR] Failed to add money or update status: " . $ex->getMessage() . "\n", FILE_APPEND);
                $this->json(['success' => false, 'message' => 'Internal Error'], 500);
            }
        } else {
            file_put_contents($logFile, "[NOT_FOUND] Could not find any pending deposit request matching content or user.\n", FILE_APPEND);
            $this->json(['success' => false, 'message' => 'No matching deposit request found'], 404);
        }
    }
}
