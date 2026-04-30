<?php
/**
 * Cron Job: Tự động release tiền đã hết hạn giữ
 * Chạy mỗi giờ: 0 * * * * php /path/to/cron_release_funds.php
 */

require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Model.php';
require_once __DIR__ . '/app/Models/HeldFund.php';
require_once __DIR__ . '/app/Models/SellerDeposit.php';
require_once __DIR__ . '/app/Services/WalletService.php';
require_once __DIR__ . '/app/Services/EscrowService.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting auto-release funds job...\n";

try {
    $escrowService = new EscrowService();
    $result = $escrowService->autoReleaseExpiredFunds();
    
    if ($result['success']) {
        echo "[" . date('Y-m-d H:i:s') . "] Released {$result['released_count']} expired funds\n";
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] Error: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Exception: " . $e->getMessage() . "\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Job completed\n";
