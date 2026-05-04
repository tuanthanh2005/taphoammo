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
        
        // Tùy theo cách SePay gửi token, có thể qua Header Authorization hoặc trong body
        $headers = getallheaders();
        $receivedToken = '';
        if (isset($headers['Authorization'])) {
            // SePay có thể gửi 'Apikey YOUR_TOKEN' hoặc 'Bearer YOUR_TOKEN'
            $receivedToken = $headers['Authorization'];
            $receivedToken = str_replace(['Bearer ', 'Apikey '], '', $receivedToken);
        }

        if ($webhookToken !== '' && $receivedToken !== $webhookToken) {
            Logger::error('SePay Webhook: Invalid token');
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        // Xử lý giao dịch
        // SePay gửi nội dung chuyển khoản trong trường 'content' hoặc 'description'
        $content = $data['content'] ?? $data['description'] ?? '';
        $amount = (float)($data['transferAmount'] ?? $data['amount_in'] ?? 0);
        $transactionId = $data['id'] ?? $data['transaction_id'] ?? '';

        if ($amount <= 0) {
            $this->json(['success' => true, 'message' => 'Ignored zero amount']);
            return;
        }

        // Tìm yêu cầu nạp tiền khớp với nội dung chuyển khoản
        // Nội dung thường có dạng NAP0001_XXXX
        $depositRequestModel = new DepositRequest();
        
        // Thử tìm theo transfer_code (nội dung chuyển khoản)
        $deposit = $db->fetchOne("SELECT * FROM deposit_requests WHERE transfer_code = ? AND status = 'pending'", [$content]);

        // Nếu không tìm thấy bằng nội dung chính xác, thử tìm theo pattern trong description
        if (!$deposit) {
            // SePay có thể gộp nhiều thứ vào description, ta thử regex để tìm mã NAPxxxx
            if (preg_match('/NAP\d+_[A-Z0-9]{4}/i', $content, $matches)) {
                $foundCode = strtoupper($matches[0]);
                $deposit = $db->fetchOne("SELECT * FROM deposit_requests WHERE transfer_code = ? AND status = 'pending'", [$foundCode]);
            }
        }

        if ($deposit) {
            file_put_contents($logFile, "[MATCHED] Found deposit request ID: " . $deposit['id'] . "\n", FILE_APPEND);
            // Kiểm tra số tiền (cho phép sai lệch nhỏ nếu cần, nhưng thường bank auto thì nên khớp)
            if (abs($amount - (float)$deposit['amount']) > 1) {
                Logger::error("SePay Webhook: Amount mismatch. Expected {$deposit['amount']}, got {$amount}");
                // Chúng ta vẫn có thể duyệt nếu admin muốn, hoặc treo lại. 
                // Ở đây ta cứ duyệt đúng số tiền thực nhận.
            }

            // Thực hiện cộng tiền
            $walletService = new WalletService();
            $walletService->addMoney(
                $deposit['user_id'],
                $amount,
                'deposit',
                'deposit_request',
                $deposit['id'],
                "Nạp tiền tự động qua SePay (GD: $transactionId)"
            );

            // Cập nhật trạng thái yêu cầu
            $depositRequestModel->update($deposit['id'], [
                'status' => 'approved',
                'admin_note' => "Duyệt tự động qua SePay. GD: $transactionId",
                'processed_at' => date('Y-m-d H:i:s')
            ]);

            Logger::activity("SePay: Automatically approved deposit #{$deposit['id']} for User #{$deposit['user_id']}");
            
            $this->json(['success' => true, 'message' => 'Processed successfully']);
        } else {
            file_put_contents($logFile, "[NOT_FOUND] No pending deposit request for content: $content\n", FILE_APPEND);
            Logger::error("SePay Webhook: No pending deposit request found for content '$content'");
            $this->json(['success' => false, 'message' => 'No matching deposit request found'], 404);
        }
    }
}
