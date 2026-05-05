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

        // Thử tìm chính xác theo nội dung
        $deposit = $db->fetchOne("SELECT * FROM deposit_requests WHERE transfer_code = ? AND status = 'pending'", [$content]);

        // Nếu không thấy, dùng Regex để bóc tách mã nạp tiền từ nội dung chuyển khoản
        if (!$deposit) {
            // Hỗ trợ các định dạng: NAP123_ABCD, NAPUSER 123, NAPSELLER 123, NAP00062377
            if (preg_match('/(NAPUSER\s*\d+|NAPSELLER\s*\d+|NAP\d+_[A-Z0-9]+|NAP\d+)/i', $content, $matches)) {
                $foundCode = strtoupper(trim($matches[0]));
                file_put_contents($logFile, "[DEBUG] Extracted code: $foundCode\n", FILE_APPEND);
                // Chuẩn hóa dấu cách nếu có (Ví dụ "NAPUSER 1" thành "NAPUSER 1")
                $deposit = $db->fetchOne("SELECT * FROM deposit_requests WHERE (transfer_code = ? OR transfer_code = ?) AND status = 'pending'", [$foundCode, str_replace(' ', '', $foundCode)]);
            }
        }

        if ($deposit) {
            file_put_contents($logFile, "[MATCHED] Found deposit request ID: " . $deposit['id'] . " by transfer_code\n", FILE_APPEND);
        } else {
            // Nếu vẫn không thấy, thử bóc tách User ID từ mã NAP (Ví dụ: NAP00063960 -> 63960)
            if (preg_match('/NAP(?:USER|SELLER)?\s*0*(\d+)/i', $foundCode ?? '', $idMatches)) {
                $userId = $idMatches[1];
                file_put_contents($logFile, "[DEBUG] Falling back to search by User ID: $userId\n", FILE_APPEND);
                $deposit = $db->fetchOne(
                    "SELECT * FROM deposit_requests WHERE user_id = ? AND status = 'pending' ORDER BY id DESC LIMIT 1",
                    [$userId]
                );
                if ($deposit) {
                    file_put_contents($logFile, "[MATCHED] Found latest pending request ID: " . $deposit['id'] . " for User ID: $userId\n", FILE_APPEND);
                }
            }
        }

        if ($deposit) {
            file_put_contents($logFile, "[MATCHED] Final match for deposit request ID: " . $deposit['id'] . "\n", FILE_APPEND);
            
            // Thực hiện cộng tiền
            $walletService = new WalletService();
            // Xác định loại ví (User hay Seller)
            $walletType = (strpos(strtoupper($deposit['transfer_code']), 'NAPSELLER') !== false) ? 'seller_deposit' : 'deposit';
            
            $walletService->addMoney(
                $deposit['user_id'],
                $amount,
                'deposit', // Kiểu giao dịch (thường là deposit cho cả 2)
                'deposit_request',
                $deposit['id'],
                "Nạp tiền tự động qua SePay (GD: $transactionId)"
            );

            // Cập nhật trạng thái yêu cầu nạp tiền
            $depositRequestModel->update($deposit['id'], [
                'status' => 'approved',
                'admin_note' => "Duyệt tự động qua SePay. GD: $transactionId",
                'processed_at' => date('Y-m-d H:i:s')
            ]);

            Logger::activity("SePay: Automatically approved deposit #{$deposit['id']} for User #{$deposit['user_id']}");
            $this->json(['success' => true, 'message' => 'Processed successfully']);
        } else {
            file_put_contents($logFile, "[NOT_FOUND] No pending deposit request for content: $content\n", FILE_APPEND);
            $this->json(['success' => false, 'message' => 'No matching deposit request found'], 404);
        }
    }
}
