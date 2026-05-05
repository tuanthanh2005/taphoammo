<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$bankCode = trim($walletSettings['deposit_bank_code'] ?? 'KienLongBank');
$bankName = trim($walletSettings['deposit_bank_name'] ?? 'KienLongBank');
$accountName = trim($walletSettings['deposit_account_name'] ?? 'TRAN THANH TUAN');
$accountNumber = trim($walletSettings['deposit_account_number'] ?? '101499100004608842');
$supportTelegram = trim($walletSettings['wallet_telegram_support_username'] ?? $walletSettings['telegram_support_username'] ?? '@specademy');
$supportTelegramUrl = trim($walletSettings['wallet_telegram_support_url'] ?? $walletSettings['telegram_support_url'] ?? 'https://t.me/specademy');
$baseTransferContent = 'NAP' . str_pad((string) Auth::id(), 4, '0', STR_PAD_LEFT);
$depositPresets = [50000, 100000, 200000, 500000, 1000000, 2000000];
?>

<div class="wallet-page py-4">
    <div class="container">
        <!-- Balance Header -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-gradient-wallet text-white overflow-hidden">
                    <div class="card-body p-4 position-relative z-1">
                        <div class="small opacity-75 mb-1">Số dư hiện tại</div>
                        <h1 class="display-5 fw-bold mb-0"><?= money($wallet['balance'] ?? 0) ?></h1>
                        <div class="mt-3">
                            <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-2">
                                <i class="fas fa-coins me-1"></i> Đang hoạt động
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row h-100">
                    <div class="col-6 col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-3">
                            <div class="text-muted small mb-1">Tổng đã nạp</div>
                            <h4 class="fw-bold mb-0 text-success"><?= money($wallet['total_earned'] ?? 0) ?></h4>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-3">
                            <div class="text-muted small mb-1">Nạp tối thiểu</div>
                            <h4 class="fw-bold mb-0">50.000đ</h4>
                        </div>
                    </div>
                    <div class="col-md-4 d-none d-md-block">
                        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-light border-0">
                            <div class="d-flex align-items-center h-100">
                                <div class="bg-primary text-white rounded-circle p-2 me-3">
                                    <i class="fab fa-telegram-plane"></i>
                                </div>
                                <div class="text-start">
                                    <div class="small text-muted">Hỗ trợ nạp tiền</div>
                                    <a href="<?= e($supportTelegramUrl) ?>" target="_blank" class="fw-bold text-primary text-decoration-none small"><?= e($supportTelegram) ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left: Deposit Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>Nạp tiền vào tài khoản</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-warning border-0 rounded-4 mb-4 small">
                            <i class="fas fa-exclamation-triangle me-2"></i> <strong>Lưu ý:</strong> Mỗi người dùng chỉ được gửi tối đa <strong>5 yêu cầu nạp tiền</strong> trong vòng 1 tiếng.
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">1. Chọn mệnh giá</label>
                            <div class="row g-2">
                                <?php foreach ($depositPresets as $preset): ?>
                                    <div class="col-4 col-md-2">
                                        <button type="button" class="btn btn-outline-light text-dark w-100 py-3 rounded-3 border fw-bold preset-btn" data-amount="<?= $preset ?>">
                                            <?= number_format($preset / 1000, 0) ?>K
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">2. Nhập số tiền</label>
                            <div class="input-group input-group-lg shadow-none">
                                <input type="number" id="depositAmount" class="form-control bg-light border-0 fw-bold" 
                                       min="50000" max="5000000" step="1000" placeholder="Số tiền muốn nạp (50k - 5M)">
                                <span class="input-group-text bg-light border-0 text-muted fw-bold">VNĐ</span>
                            </div>
                        </div>

                        <div class="d-grid gap-3">
                            <button type="button" id="generateQrBtn" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold shadow-sm">
                                <i class="fas fa-qrcode me-2"></i>TẠO MÃ QR NẠP TIỀN
                            </button>
                        </div>

                        <!-- QR Result Container -->
                        <div id="walletQrResult" class="mt-5 pt-4 border-top d-none">
                            <div class="row g-4 justify-content-center">
                                <div class="col-md-5">
                                    <div class="bg-light p-3 rounded-4 shadow-sm text-center">
                                        <img id="generatedQrImage" src="" alt="VietQR" class="img-fluid rounded-3 mb-2" style="max-height: 300px;">
                                        <div class="small text-muted"><i class="fas fa-expand-arrows-alt"></i> Quét mã bằng App Ngân hàng</div>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="d-grid gap-3">
                                        <div class="p-3 bg-light rounded-4 border">
                                            <div class="small text-muted mb-1">Số tiền</div>
                                            <h4 class="fw-bold mb-0 text-primary" id="generatedAmountText">0đ</h4>
                                        </div>
                                        <div class="p-3 bg-light rounded-4 border">
                                            <div class="small text-muted mb-1">Nội dung chuyển khoản</div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h4 class="fw-bold mb-0 font-monospace text-dark" id="generatedTransferContent">---</h4>
                                                <button type="button" class="btn btn-sm btn-dark rounded-3 px-3 wallet-copy-btn" id="copyContentBtn">
                                                    <i class="fas fa-copy me-1"></i> Sao chép
                                                </button>
                                            </div>
                                        </div>
                                        <div class="alert alert-info border-0 rounded-4 mb-3 small">
                                            <i class="fas fa-info-circle me-2"></i> Vui lòng giữ nguyên nội dung chuyển khoản để hệ thống tự động xử lý nhanh nhất.
                                        </div>

                                        <button type="button" id="confirmTransferBtn" class="btn btn-success btn-lg w-100 rounded-pill py-3 fw-bold d-none shadow-sm">
                                            <i class="fas fa-check-circle me-2"></i>XÁC NHẬN ĐÃ CHUYỂN KHOẢN
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Lịch sử giao dịch</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-nowrap">
                                <thead class="bg-light">
                                    <tr class="small text-uppercase fw-bold text-muted">
                                        <th class="ps-4">Mã GD</th>
                                        <th>Loại</th>
                                        <th>Số tiền</th>
                                        <th>Số dư sau</th>
                                        <th>Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $history = [];
                                foreach ($transactions as $tx) {
                                    $history[] = [
                                        'id' => '#' . $tx['id'],
                                        'description' => $tx['description'],
                                        'amount' => (float)$tx['amount'],
                                        'balance_after' => (float)$tx['balance_after'],
                                        'created_at' => $tx['created_at'],
                                        'type' => $tx['type'],
                                        'display_status' => 'success'
                                    ];
                                }
                                foreach ($depositRequests as $dr) {
                                    if ($dr['status'] !== 'approved') {
                                        $history[] = [
                                            'id' => 'YQ' . $dr['id'],
                                            'description' => $dr['status'] === 'rejected' ? 'Nạp tiền thất bại' : 'Đang chờ xử lý',
                                            'amount' => (float)$dr['amount'],
                                            'balance_after' => null,
                                            'created_at' => $dr['created_at'],
                                            'type' => 'deposit',
                                            'display_status' => $dr['status'],
                                            'admin_note' => $dr['admin_note']
                                        ];
                                    }
                                }
                                usort($history, function($a, $b) {
                                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                                });
                                ?>

                                <?php if (empty($history)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">Chưa có giao dịch nào</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($history as $item): ?>
                                        <?php
                                        $isMinus = in_array($item['type'], ['purchase', 'admin_fee', 'withdrawal', 'withdrawal_fee'], true);
                                        $statusClass = 'bg-light text-dark';
                                        if ($item['display_status'] === 'rejected') $statusClass = 'bg-danger-subtle text-danger';
                                        if ($item['display_status'] === 'pending') $statusClass = 'bg-warning-subtle text-warning';
                                        ?>
                                        <tr>
                                            <td class="ps-4 fw-bold"><?= $item['id'] ?></td>
                                            <td>
                                                <span class="badge rounded-pill px-3 <?= $statusClass ?> border-0">
                                                    <?= e($item['description']) ?>
                                                </td>
                                            <td>
                                                <span class="fw-bold <?= $isMinus ? 'text-danger' : 'text-success' ?>">
                                                    <?= $isMinus ? '-' : '+' ?><?= number_format($item['amount']) ?>đ
                                                </span>
                                                <?php if (!empty($item['admin_note'])): ?>
                                                    <div class="small text-muted" style="font-size: 10px;">Lý do: <?= e($item['admin_note']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted fw-bold"><?= $item['balance_after'] !== null ? number_format($item['balance_after']) . 'đ' : '---' ?></td>
                                            <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Bank Info -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-light border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-university me-2 text-primary"></i>Tài khoản nhận tiền (Admin)</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-grid gap-4">
                            <div class="bank-item">
                                <div class="small text-muted mb-1">Ngân hàng</div>
                                <div class="fw-bold text-dark fs-5"><?= e($bankName) ?></div>
                            </div>
                            <div class="bank-item">
                                <div class="small text-muted mb-1">Số tài khoản</div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="fw-bold text-dark fs-5"><?= e($accountNumber) ?></div>
                                    <button class="btn btn-sm btn-outline-dark border-0 wallet-copy-btn" data-copy="<?= e($accountNumber) ?>"><i class="fas fa-copy"></i></button>
                                </div>
                            </div>
                            <div class="bank-item">
                                <div class="small text-muted mb-1">Chủ tài khoản</div>
                                <div class="fw-bold text-dark fs-5 text-uppercase"><?= e($accountName) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white overflow-hidden p-4 text-center">
                    <div class="position-relative z-1">
                        <h5 class="fw-bold mb-3">Cần hỗ trợ gấp?</h5>
                        <p class="small opacity-75 mb-4">Gặp lỗi khi nạp tiền? Liên hệ ngay Telegram hỗ trợ khách hàng 24/7.</p>
                        <a href="<?= e($supportTelegramUrl) ?>" target="_blank" class="btn btn-light text-primary w-100 py-3 fw-bold rounded-pill shadow-none">
                            <i class="fab fa-telegram-plane me-1"></i> NHẮN TIN NGAY
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-wallet {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
}
.preset-btn.active {
    background-color: #6366f1 !important;
    color: white !important;
    border-color: #6366f1 !important;
    box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3) !important;
}
.bank-item {
    padding-bottom: 1rem;
    border-bottom: 1px dashed rgba(0,0,0,0.05);
}
.bank-item:last-child { border-bottom: none; }
.card { transition: transform 0.2s ease; }
.card:hover { transform: translateY(-2px); }

/* Pulse Loader */
.pulse-loader {
    width: 20px;
    height: 20px;
    background-color: #6366f1;
    border-radius: 50%;
    position: relative;
}
.pulse-loader::after {
    content: "";
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: inherit;
    border-radius: inherit;
    animation: pulse 1.5s ease-out infinite;
}
@keyframes pulse {
    0% { transform: scale(1); opacity: 0.8; }
    100% { transform: scale(3.5); opacity: 0; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const presets = document.querySelectorAll('.preset-btn');
    const amountInput = document.getElementById('depositAmount');
    const generateBtn = document.getElementById('generateQrBtn');
    const confirmBtn = document.getElementById('confirmTransferBtn');
    const qrResult = document.getElementById('walletQrResult');
    const qrImage = document.getElementById('generatedQrImage');
    const amountText = document.getElementById('generatedAmountText');
    const contentDisplay = document.getElementById('generatedTransferContent');
    const copyBtn = document.getElementById('copyContentBtn');
    
    let currentTransferContent = '';
    let pollingInterval = null;
    const baseTransferContent = '<?= $baseTransferContent ?>';
    const bankCode = '<?= $bankCode ?>';
    const accountNumber = '<?= $accountNumber ?>';
    const accountName = '<?= $accountName ?>';

    function formatMoney(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
    }

    presets.forEach(btn => {
        btn.addEventListener('click', () => {
            presets.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            amountInput.value = btn.dataset.amount;
        });
    });

    amountInput.addEventListener('input', () => {
        presets.forEach(b => b.classList.remove('active'));
    });

    generateBtn.addEventListener('click', async () => {
        const amount = parseInt(amountInput.value);
        
        if (pollingInterval) clearInterval(pollingInterval);

        if (isNaN(amount) || amount < 50000 || amount > 5000000) {
            Swal.fire('Lỗi', 'Số tiền từ 50k - 5M', 'warning');
            return;
        }

        generateBtn.disabled = true;
        generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang tạo...';

        const formData = new FormData();
        formData.append('amount', amount);
        formData.append('csrf_token', '<?= csrf_token() ?>');

        try {
            const response = await fetch('<?= url('/user/wallet/initiate-deposit') ?>', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                currentTransferContent = result.transfer_code;
                qrImage.src = result.qr_url;
                amountText.textContent = formatMoney(result.amount);
                contentDisplay.textContent = result.transfer_code;
                copyBtn.dataset.copy = result.transfer_code;

                qrResult.classList.remove('d-none');
                
                // If it's SePay, we might want to hide the manual confirm button or show a message
                if (result.is_sepay || bankCode.toLowerCase() === 'kienlongbank' || bankCode.toLowerCase() === 'klb') {
                    document.getElementById('autoLoadingStatus').classList.remove('d-none');
                    confirmBtn.classList.add('d-none');
                    const alertBox = document.getElementById('depositInfoBox');
                    alertBox.innerHTML = '<i class="fas fa-magic me-2"></i> Hệ thống SePay sẽ tự động cộng tiền sau khi bạn chuyển khoản thành công.';
                    alertBox.className = 'alert alert-success border-0 rounded-4 mb-3 small';
                    startPolling(result.transfer_code);
                } else {
                    document.getElementById('autoLoadingStatus').classList.add('d-none');
                    confirmBtn.classList.remove('d-none');
                }

                qrResult.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                Swal.fire('Lỗi', result.message || 'Không thể tạo yêu cầu', 'error');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Lỗi', 'Không thể kết nối máy chủ', 'error');
        } finally {
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="fas fa-qrcode me-2"></i>TẠO MÃ QR NẠP TIỀN';
        }
    });

    function startPolling(transferCode) {
        if (pollingInterval) clearInterval(pollingInterval);
        
        pollingInterval = setInterval(async () => {
            try {
                const response = await fetch(`<?= url('/api/deposit/check-status') ?>?code=${transferCode}`);
                const result = await response.json();
                
                if (result.success && result.status === 'approved') {
                    clearInterval(pollingInterval);
                    Swal.fire({
                        title: 'Thành công!',
                        text: 'Tiền đã được cộng vào tài khoản của bạn.',
                        icon: 'success',
                        confirmButtonText: 'Tuyệt vời'
                    }).then(() => {
                        location.reload();
                    });
                }
            } catch (err) {
                console.error('Polling error:', err);
            }
        }, 3000);
    }

    confirmBtn.addEventListener('click', async () => {
        const amount = parseInt(amountInput.value);
        const { isConfirmed } = await Swal.fire({
            title: 'Xác nhận nạp tiền?',
            text: `Bạn cam kết đã chuyển khoản đúng ${formatMoney(amount)}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Đã chuyển',
            cancelButtonText: 'Chưa'
        });

        if (isConfirmed) {
            const formData = new FormData();
            formData.append('amount', amount);
            formData.append('transfer_code', currentTransferContent);
            formData.append('csrf_token', '<?= csrf_token() ?>');

            try {
                const response = await fetch('<?= url('/user/wallet/confirm-deposit') ?>', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('Thành công', 'Yêu cầu đã gửi. Chờ admin duyệt.', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Lỗi', result.message || 'Đã xảy ra lỗi không xác định', 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Lỗi', 'Không thể kết nối máy chủ hoặc phản hồi không hợp lệ', 'error');
            }
        }
    });

    document.querySelectorAll('.wallet-copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const text = btn.dataset.copy || btn.previousElementSibling?.textContent;
            navigator.clipboard.writeText(text).then(() => {
                const oldHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => btn.innerHTML = oldHtml, 2000);
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
