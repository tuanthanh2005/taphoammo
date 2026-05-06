<?php ob_start(); ?>
<?php
$bankCode = trim($walletSettings['deposit_bank_code'] ?? 'KienLongBank');
$bankName = trim($walletSettings['deposit_bank_name'] ?? 'KienLongBank');
$accountName = trim($walletSettings['deposit_account_name'] ?? 'TRAN THANH TUAN');
$accountNumber = trim($walletSettings['deposit_account_number'] ?? '101499100004608842');
$walletSupportTelegram = trim($walletSettings['wallet_telegram_support_username'] ?? '@specademy');
$walletSupportTelegramUrl = trim($walletSettings['wallet_telegram_support_url'] ?? 'https://t.me/specademy');
?>

<div class="row mb-4 gy-3">
    <div class="col-12 col-md-auto me-auto">
        <h2 class="mb-0 fw-bold"><i class="fas fa-wallet text-primary me-2"></i>Ví tiền của tôi</h2>
    </div>
    <div class="col-12 col-md-auto d-flex gap-2">
        <button class="btn btn-primary flex-fill" data-bs-toggle="modal" data-bs-target="#depositModal">
            <i class="fas fa-plus-circle"></i> Nạp tiền
        </button>
        <button class="btn btn-outline-danger flex-fill" data-bs-toggle="modal" data-bs-target="#deactivateModal">
            <i class="fas fa-user-times"></i> Hủy Seller
        </button>
    </div>
</div>

<?php
// Kiểm tra yêu cầu deactivation
$deactivationService = new SellerDeactivationService();
$deactivationRequest = $deactivationService->getSellerRequest(Auth::id());
?>

<?php if ($deactivationRequest): ?>
<div class="alert alert-warning">
    <h5><i class="fas fa-exclamation-triangle"></i> Yêu cầu hủy tài khoản đang chờ xử lý</h5>
    <p class="mb-2">Bạn sẽ nhận lại <strong><?= money($deactivationRequest['refund_amount']) ?></strong> vào ngày <strong><?= date('d/m/Y', strtotime($deactivationRequest['hold_until'])) ?></strong></p>
    <p class="mb-0 small text-muted">Trong thời gian chờ, tài khoản của bạn bị tạm khóa không thể bán hàng.</p>
    <form action="<?= url('/seller/deactivation/cancel') ?>" method="POST" class="mt-2">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-secondary">Hủy yêu cầu</button>
    </form>
</div>
<?php endif; ?>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> <strong>Cơ chế hoạt động:</strong>
    <ul class="mb-0 mt-1 small">
        <li>Khi nhập stock: Số tiền = <strong>số lượng × giá sản phẩm</strong> sẽ bị trừ vào <strong>tiền cọc</strong></li>
        <li>Khi khách mua: Tiền bán hàng vào <strong>tiền đang giữ</strong> (7 ngày), tiền cọc tương ứng được hoàn lại vào số dư</li>
        <li>Sau 7 ngày: Tiền tự động chuyển vào <strong>số dư khả dụng</strong>, rút được (trừ 5% phí rút)</li>
    </ul>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-white bg-success shadow-sm h-100 border-0">
            <div class="card-body p-3">
                <h6 class="card-title mb-1 opacity-75 small"><i class="fas fa-wallet"></i> Khả dụng</h6>
                <h3 class="mb-0 fw-bold" style="font-size: calc(1.1rem + 0.3vw);"><?= money($wallet['balance'] ?? 0) ?></h3>
            </div>
            <div class="card-footer bg-white bg-opacity-10 border-0 p-2 text-center">
                <a href="<?= url('/seller/withdrawals') ?>" class="text-white text-decoration-none small fw-bold">Rút tiền <i class="fas fa-chevron-right ms-1"></i></a>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-white bg-warning shadow-sm h-100 border-0">
            <div class="card-body p-3">
                <h6 class="card-title mb-1 opacity-75 small"><i class="fas fa-hourglass-half"></i> Đang giữ</h6>
                <h3 class="mb-0 fw-bold" style="font-size: calc(1.1rem + 0.3vw);"><?= money($wallet['held_balance'] ?? 0) ?></h3>
                <div class="mt-1 opacity-75" style="font-size: 0.7rem;">7 ngày xử lý</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-white bg-info shadow-sm h-100 border-0">
            <div class="card-body p-3">
                <h6 class="card-title mb-1 opacity-75 small"><i class="fas fa-shield-alt"></i> Tiền cọc</h6>
                <h3 class="mb-0 fw-bold" style="font-size: calc(1.1rem + 0.3vw);"><?= money($wallet['deposit_balance'] ?? 0) ?></h3>
                <div class="mt-1 opacity-75" style="font-size: 0.7rem;">Dùng cho stock</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-white bg-primary shadow-sm h-100 border-0">
            <div class="card-body p-3">
                <h6 class="card-title mb-1 opacity-75 small"><i class="fas fa-chart-line"></i> Tổng thu nhập</h6>
                <h3 class="mb-0 fw-bold" style="font-size: calc(1.1rem + 0.3vw);"><?= money($wallet['total_earned'] ?? 0) ?></h3>
                <div class="mt-1 opacity-75" style="font-size: 0.7rem;">Lũy kế</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-history"></i> Lịch sử giao dịch gần đây</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-2" style="min-width: 80px;">Mã/Loại</th>
                        <th style="min-width: 90px;">Số tiền</th>
                        <th style="min-width: 90px;">Số dư sau</th>
                        <th style="min-width: 120px;">Mô tả</th>
                        <th style="min-width: 70px;">Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">Chưa có giao dịch nào</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td class="ps-2">
                                    <div class="fw-bold small text-muted" style="font-size: 0.65rem;">#<?= $tx['id'] ?></div>
                                    <?php
                                    $typeLabels = [
                                        'deposit' => ['success', 'Nạp tiền'],
                                        'purchase' => ['danger', 'Mua hàng'],
                                        'sale_income' => ['success', 'Bán hàng'],
                                        'admin_fee' => ['danger', 'Phí sàn'],
                                        'withdrawal' => ['warning', 'Rút tiền'],
                                        'withdrawal_fee' => ['danger', 'Phí rút'],
                                        'refund' => ['info', 'Hoàn tiền'],
                                        'affiliate_commission' => ['success', 'Hoa hồng']
                                    ];
                                    $label = $typeLabels[$tx['type']] ?? ['secondary', $tx['type']];
                                    ?>
                                    <span class="badge bg-<?= $label[0] ?> py-1 px-1" style="font-size: 0.6rem;"><?= $label[1] ?></span>
                                </td>
                                <td>
                                    <?php if ($tx['amount'] < 0): ?>
                                        <div class="text-danger fw-bold" style="font-size: 0.75rem;"><?= money($tx['amount']) ?></div>
                                    <?php else: ?>
                                        <div class="text-success fw-bold" style="font-size: 0.75rem;">+<?= money($tx['amount']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-muted" style="font-size: 0.75rem;"><?= money($tx['balance_after']) ?></td>
                                <td><div class="small text-muted" style="font-size: 0.7rem; line-height: 1.2; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= e($tx['description']) ?></div></td>
                                <td>
                                    <div class="fw-bold" style="font-size: 0.7rem;"><?= date('H:i', strtotime($tx['created_at'])) ?></div>
                                    <div class="text-muted" style="font-size: 0.6rem;"><?= date('d/m/y', strtotime($tx['created_at'])) ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nạp Tiền -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-wallet text-success me-2"></i> Nạp tiền vào ví</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/seller/wallet/deposit') ?>" method="POST" id="depositForm">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div id="depositStep1">
                        <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info small mb-4">
                            <i class="fas fa-info-circle me-1"></i> Nhập số tiền cần nạp để tạo mã QR chuyển khoản tự động.
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted">Số tiền nạp <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden">
                                <input type="number" name="amount" id="depositAmount" class="form-control border-0 bg-light" placeholder="0" min="100000" step="1000" required>
                                <span class="input-group-text border-0 bg-light fw-bold text-muted">VNĐ</span>
                            </div>
                            <div class="mt-2 text-danger small fw-semibold">
                                <i class="fas fa-exclamation-circle"></i> Tối thiểu: 100,000 VNĐ
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted">Chọn nhanh:</label>
                            <div class="row g-2">
                                <div class="col-6"><button type="button" class="btn btn-outline-success w-100 py-2 rounded-3" onclick="setAmount(100000)">100,000 đ</button></div>
                                <div class="col-6"><button type="button" class="btn btn-outline-success w-100 py-2 rounded-3" onclick="setAmount(500000)">500,000 đ</button></div>
                                <div class="col-6"><button type="button" class="btn btn-outline-success w-100 py-2 rounded-3" onclick="setAmount(1000000)">1,000,000 đ</button></div>
                                <div class="col-6"><button type="button" class="btn btn-outline-success w-100 py-2 rounded-3" onclick="setAmount(5000000)">5,000,000 đ</button></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted">Ghi chú (tùy chọn)</label>
                            <textarea name="note" class="form-control border-0 bg-light rounded-3" rows="2" placeholder="Ví dụ: Nạp tiền để trả cọc stock"></textarea>
                        </div>

                        <div class="d-grid pt-2">
                            <button type="button" class="btn btn-success btn-lg fw-bold shadow-sm py-3 rounded-3" onclick="generateQR()">
                                XÁC NHẬN & LẤY MÃ QR <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <div id="depositStep2" class="d-none text-center animate__animated animate__fadeIn">
                        <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success py-2 mb-3 small fw-bold">
                            <i class="fas fa-check-circle me-1"></i> ĐÃ TẠO MÃ THANH TOÁN
                        </div>
                        
                        <div class="qr-container p-2 border rounded-4 bg-white shadow-sm mb-3 mx-auto" style="max-width: 280px;">
                            <img id="qrImage" src="" alt="VietQR" class="img-fluid rounded-3">
                        </div>
                        <div class="mb-3 small text-muted"><i class="fas fa-mobile-alt me-1"></i> Mở App ngân hàng quét mã để thanh toán</div>

                        <div class="payment-info text-start border-0 rounded-4 p-3 mb-4 bg-light shadow-sm">
                            <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                                <span class="text-muted small">Ngân hàng</span>
                                <span class="fw-bold"><?= e($bankName) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                                <span class="text-muted small">Số tài khoản</span>
                                <span class="fw-bold text-primary"><?= e($accountNumber) ?> <i class="far fa-copy ms-1 cursor-pointer text-muted" onclick="copyText('<?= e($accountNumber) ?>')"></i></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                                <span class="text-muted small">Số tiền</span>
                                <span class="fw-bold text-danger fs-5" id="displayAmount">0 đ</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Nội dung</span>
                                <span class="fw-bold text-danger" id="displayMemo">NAPSELLER <?= Auth::id() ?> <i class="far fa-copy ms-1 cursor-pointer text-muted" onclick="copyText('NAPSELLER <?= Auth::id() ?>')"></i></span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <!-- Auto Loading Status -->
                            <div id="autoLoadingStatus" class="mb-3">
                                <div class="d-flex align-items-center justify-content-center p-3 bg-white rounded-4 border shadow-sm">
                                    <div class="pulse-loader me-3"></div>
                                    <div class="text-start">
                                        <div class="fw-bold text-primary">Đang chờ thanh toán...</div>
                                        <div class="small text-muted">Hệ thống sẽ tự động cộng tiền khi nhận được</div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-link text-muted text-decoration-none small" onclick="backToStep1()">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại sửa số tiền
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hủy Tài Khoản Seller -->
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Hủy Tài Khoản Seller</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/seller/deactivation/request') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Cảnh báo:</strong> Hành động này sẽ:
                        <ul class="mb-0 mt-2">
                            <li>Đóng băng tài khoản seller của bạn</li>
                            <li>Không thể bán hàng trong 7 ngày</li>
                            <li>Sau 7 ngày, bạn sẽ nhận lại toàn bộ số dư (bao gồm tiền cọc)</li>
                            <li>Tài khoản sẽ chuyển về tài khoản thường (user)</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Số dư sẽ được hoàn:</label>
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control fw-bold text-success" value="<?= money(($wallet['balance'] ?? 0) + ($wallet['deposit_balance'] ?? 0)) ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Lý do hủy (tùy chọn)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Ví dụ: Không muốn bán hàng nữa, chuyển sang nền tảng khác..."></textarea>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmDeactivate" required>
                        <label class="form-check-label" for="confirmDeactivate">
                            Tôi hiểu và đồng ý với các điều khoản trên
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận hủy tài khoản</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let pollingInterval = null;

function setAmount(amount) {
    document.getElementById('depositAmount').value = amount;
}

async function generateQR() {
    const amount = document.getElementById('depositAmount').value;
    const generateBtn = document.querySelector('button[onclick="generateQR()"]');
    
    if (!amount || amount < 100000) {
        alert('Vui lòng nhập số tiền tối thiểu 100,000đ');
        return;
    }

    generateBtn.disabled = true;
    generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> ĐANG XỬ LÝ...';

    const sellerId = '<?= Auth::id() ?>';
    const memo = 'NAPSELLER ' + sellerId;
    const accountNumber = '<?= e($accountNumber) ?>';
    const accountName = '<?= e($accountName) ?>';
    const bankCode = '<?= e($bankCode) ?>';

    // Gọi API để tạo yêu cầu trong database trước
    const formData = new FormData();
    formData.append('amount', amount);
    formData.append('transfer_code', memo);
    formData.append('csrf_token', '<?= csrf_token() ?>');

    try {
        await fetch('<?= url('/seller/wallet/deposit') ?>', {
            method: 'POST',
            body: formData
        });
    } catch (e) { console.error(e); }

    let qrUrl = '';
    if (bankCode.toLowerCase() === 'kienlongbank' || bankCode.toLowerCase() === 'klb') {
        qrUrl = `https://qr.sepay.vn/img?acc=${accountNumber}&bank=KienLongBank&amount=${amount}&des=${encodeURIComponent(memo)}`;
    } else {
        qrUrl = `https://img.vietqr.io/image/${bankCode}-${accountNumber}-compact2.png?amount=${amount}&addInfo=${encodeURIComponent(memo)}&accountName=${encodeURIComponent(accountName)}`;
    }
    
    document.getElementById('qrImage').src = qrUrl;
    document.getElementById('displayAmount').innerText = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    
    document.getElementById('autoLoadingStatus').classList.remove('d-none');
    startPolling(memo);

    document.getElementById('depositStep1').classList.add('d-none');
    document.getElementById('depositStep2').classList.remove('d-none');
    
    // Bắt đầu đếm ngược 60s cho nút quay lại (nếu cần) hoặc chỉ đơn giản là chặn click tiếp
    startCooldown(60, generateBtn);
}

function startCooldown(seconds, btn) {
    let timeLeft = seconds;
    btn.disabled = true;
    const originalText = 'XÁC NHẬN & LẤY MÃ QR <i class="fas fa-arrow-right ms-2"></i>';
    
    const timer = setInterval(() => {
        timeLeft--;
        btn.innerText = `VUI LÒNG CHỜ ${timeLeft}S...`;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }, 1000);
}

function startPolling(transferCode) {
    if (pollingInterval) clearInterval(pollingInterval);
    pollingInterval = setInterval(async () => {
        try {
            const response = await fetch(`<?= url('/api/deposit/check-status') ?>?code=${transferCode}`);
            const result = await response.json();
            if (result.success && result.status === 'approved') {
                clearInterval(pollingInterval);
                location.reload();
            }
        } catch (e) {}
    }, 3000);
}

function backToStep1() {
    if (pollingInterval) clearInterval(pollingInterval);
    document.getElementById('depositStep1').classList.remove('d-none');
    document.getElementById('depositStep2').classList.add('d-none');
}

function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Đã sao chép: ' + text);
    });
}
</script>

<style>
.cursor-pointer { cursor: pointer; }
.qr-container img {
    border-radius: 15px;
}
.animate__animated {
    animation-duration: 0.5s;
}
/* Pulse Loader */
.pulse-loader {
    width: 15px;
    height: 15px;
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

<?php
$content = ob_get_clean();
$pageTitle = 'Ví tiền';
require_once __DIR__ . '/../layouts/seller.php';
?>
