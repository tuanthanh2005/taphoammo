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

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

<div class="wallet-wrapper">
    <div class="container py-5">
        <!-- Dashboard Header -->
        <div class="row g-4 mb-5">
            <div class="col-lg-4">
                <div class="balance-card shadow-lg animate__animated animate__fadeInLeft">
                    <div class="card-glass"></div>
                    <div class="balance-content">
                        <span class="label">Số dư hiện tại</span>
                        <h1 class="amount"><?= money($wallet['balance'] ?? 0) ?></h1>
                        <div class="status-pill mt-3">
                            <span class="pulse-dot"></span>
                            Đang hoạt động
                        </div>
                    </div>
                    <div class="balance-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="row g-4 h-100">
                    <div class="col-md-4">
                        <div class="stat-box animate__animated animate__fadeInDown animate__delay-1s">
                            <div class="icon-circle bg-success-soft"><i class="fas fa-arrow-down"></i></div>
                            <div class="stat-info">
                                <span class="text-muted">Tổng đã nạp</span>
                                <h4 class="fw-bold mb-0"><?= money($wallet['total_earned'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box animate__animated animate__fadeInDown animate__delay-2s">
                            <div class="icon-circle bg-primary-soft"><i class="fas fa-hand-holding-usd"></i></div>
                            <div class="stat-info">
                                <span class="text-muted">Nạp tối thiểu</span>
                                <h4 class="fw-bold mb-0">50.000đ</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= e($supportTelegramUrl) ?>" target="_blank" class="support-card animate__animated animate__fadeInDown animate__delay-3s text-decoration-none">
                            <div class="icon-circle bg-info-soft"><i class="fab fa-telegram-plane"></i></div>
                            <div class="stat-info">
                                <span class="text-muted">Hỗ trợ 24/7</span>
                                <h4 class="fw-bold mb-0 text-primary"><?= e($supportTelegram) ?></h4>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left: Deposit Interface -->
            <div class="col-xl-8">
                <div class="main-card shadow-sm mb-4">
                    <div class="card-header-premium">
                        <div class="d-flex align-items-center">
                            <div class="header-icon"><i class="fas fa-plus-circle"></i></div>
                            <div>
                                <h4 class="mb-0 fw-bold">Nạp Tiền Vào Ví</h4>
                                <p class="text-muted small mb-0">Hệ thống nạp tiền tự động 1-3 phút</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body-premium p-4">
                        <!-- Steps -->
                        <div class="mb-5">
                            <div class="step-label">Bước 1: Chọn số tiền muốn nạp</div>
                            <div class="preset-grid mt-3">
                                <?php foreach ($depositPresets as $preset): ?>
                                    <div class="preset-item" data-amount="<?= $preset ?>">
                                        <div class="preset-box">
                                            <span class="p-amount"><?= number_format($preset / 1000, 0) ?>K</span>
                                            <span class="p-currency">VNĐ</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="custom-amount-wrapper mt-4">
                                <div class="input-group-premium">
                                    <span class="input-icon"><i class="fas fa-coins"></i></span>
                                    <input type="number" id="depositAmount" class="form-control-premium" 
                                           min="50000" max="10000000" step="1000" placeholder="Hoặc nhập số tiền khác (Tối thiểu 50k)">
                                    <span class="input-suffix text-muted">VNĐ</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mb-5">
                            <button type="button" id="generateQrBtn" class="btn-premium">
                                <span class="btn-text">TẠO MÃ QR THANH TOÁN</span>
                                <i class="fas fa-qrcode ms-2"></i>
                            </button>
                        </div>

                        <!-- QR Area (Hidden by default) -->
                        <div id="walletQrResult" class="qr-result-area d-none animate__animated animate__fadeInUp">
                            <div class="row g-4">
                                <div class="col-lg-5 text-center">
                                    <div class="qr-card">
                                        <div class="qr-frame">
                                            <img id="generatedQrImage" src="" alt="VietQR" class="img-fluid rounded-3">
                                            <div class="qr-scan-line"></div>
                                        </div>
                                        <div class="mt-3 small text-muted fw-semibold text-uppercase letter-spacing-1">
                                            <i class="fas fa-expand-arrows-alt me-1"></i> Quét mã qua App ngân hàng
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <div class="payment-details h-100 d-flex flex-column gap-3">
                                        <div class="detail-item">
                                            <label>Số tiền cần chuyển</label>
                                            <div class="value-row">
                                                <h3 class="fw-bold text-primary mb-0" id="generatedAmountText">0đ</h3>
                                                <button class="btn-copy" onclick="copyText(document.getElementById('generatedAmountText').innerText)">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <label>Nội dung chuyển khoản</label>
                                            <div class="value-row">
                                                <h4 class="fw-bold mb-0 font-monospace" id="generatedTransferContent">---</h4>
                                                <button class="btn-copy" id="copyContentBtn">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="alert-premium success mt-2" id="depositInfoBox">
                                            <div class="alert-icon"><i class="fas fa-magic"></i></div>
                                            <div class="alert-text">Hệ thống sẽ tự động cộng tiền sau khi bạn chuyển khoản thành công.</div>
                                        </div>

                                        <!-- Pulse Loader Status -->
                                        <div id="autoLoadingStatus" class="loading-status-box">
                                            <div class="loader-wrapper">
                                                <div class="pulse-loader-main"></div>
                                            </div>
                                            <div class="status-text">
                                                <div class="fw-bold text-primary">Đang chờ giao dịch...</div>
                                                <div class="small text-muted">Vui lòng không đóng trang này</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Section -->
                <div class="main-card shadow-sm overflow-hidden">
                    <div class="card-header-premium">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="header-icon"><i class="fas fa-history"></i></div>
                                <h4 class="mb-0 fw-bold">Lịch Sử Giao Dịch</h4>
                            </div>
                            <button class="btn btn-sm btn-outline-light border-0 opacity-50" onclick="location.reload()"><i class="fas fa-sync-alt"></i></button>
                        </div>
                    </div>
                    <div class="card-body-premium p-0">
                        <div class="table-responsive">
                            <table class="table-premium table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Mã GD</th>
                                        <th>Loại/Mô tả</th>
                                        <th>Biến động</th>
                                        <th>Số dư sau</th>
                                                             <?php if (empty($history)): ?>
                                    <tr><td colspan="5" class="text-center py-5 opacity-50">Chưa có giao dịch nào</td></tr>
                                <?php else: ?>
                                    <?php foreach ($history as $item): ?>
                                        <?php
                                        $type = $item['type'];
                                        $isMinus = in_array($type, ['purchase', 'admin_fee', 'withdrawal', 'withdrawal_fee'], true);
                                        
                                        // Determine display ID and status class
                                        $displayId = $item['source_table'] === 'transaction' ? '#' . $item['id'] : 'YQ' . $item['id'];
                                        $displayStatus = $item['source_table'] === 'transaction' ? 'success' : $type;
                                        
                                        $statusClass = 'badge-success';
                                        if ($displayStatus === 'rejected') $statusClass = 'badge-danger';
                                        if ($displayStatus === 'pending') $statusClass = 'badge-warning';
                                        ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark"><?= $displayId ?></td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="description-text"><?= e($item['description']) ?></span>
                                                    <?php if ($displayStatus !== 'success'): ?>
                                                        <span class="badge-mini <?= $statusClass ?>"><?= e($displayStatus) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="amount-badge <?= $isMinus ? 'minus' : 'plus' ?>">
                                                    <?= $isMinus ? '-' : '+' ?><?= number_format($item['amount']) ?>đ
                                                </span>
                                            </td>
                                            <td class="fw-bold text-muted"><?= $item['balance_after'] !== null ? number_format($item['balance_after']) . 'đ' : '---' ?></td>
                                            <td>
                                                <div class="date-text">
                                                    <i class="far fa-clock me-1"></i>
                                                    <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if ($totalPages > 1): ?>
                        <div class="card-footer-premium bg-white border-top p-3">
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm justify-content-center mb-0">
                                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Account Info -->
            <div class="col-xl-4">
                <div class="main-card shadow-sm mb-4 bg-light-gradient">
                    <div class="card-header-premium border-0">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-university me-2 text-primary"></i>Thông Tin Chuyển Khoản</h5>
                    </div>
                    <div class="card-body-premium p-4 pt-0">
                        <div class="bank-info-list">
                            <div class="info-item">
                                <span class="label">Ngân hàng nhận</span>
                                <div class="value fw-bold text-dark fs-5"><?= e($bankName) ?></div>
                            </div>
                            <div class="info-item">
                                <span class="label">Số tài khoản</span>
                                <div class="value-row">
                                    <span class="value fw-bold text-primary fs-4"><?= e($accountNumber) ?></span>
                                    <button class="btn-copy sm" onclick="copyText('<?= e($accountNumber) ?>')"><i class="fas fa-copy"></i></button>
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="label">Chủ tài khoản</span>
                                <div class="value fw-bold text-dark text-uppercase"><?= e($accountName) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="promo-card shadow-lg">
                    <div class="card-glass"></div>
                    <div class="promo-content">
                        <div class="promo-icon"><i class="fab fa-telegram-plane"></i></div>
                        <h5 class="fw-bold mb-2">Cần Hỗ Trợ Nạp Tiền?</h5>
                        <p class="small opacity-75 mb-4">Gặp sự cố khi chuyển khoản? Đừng lo lắng, chúng tôi hỗ trợ 24/7!</p>
                        <a href="<?= e($supportTelegramUrl) ?>" target="_blank" class="btn btn-light btn-lg w-100 rounded-pill fw-bold text-primary">
                            LIÊN HỆ NGAY
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-color: #6366f1;
    --primary-hover: #4f46e5;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --bg-body: #f8fafc;
    --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
}

body {
    background-color: var(--bg-body);
    font-family: 'Outfit', sans-serif;
}

.wallet-wrapper {
    background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.05), transparent),
                radial-gradient(circle at bottom left, rgba(99, 102, 241, 0.05), transparent);
}

/* Balance Card */
.balance-card {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    border-radius: 24px;
    padding: 2.5rem;
    position: relative;
    overflow: hidden;
    color: white;
    height: 100%;
    border: 1px solid rgba(255,255,255,0.1);
}

.card-glass {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: rotate 20s linear infinite;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.balance-content { position: relative; z-index: 1; }
.balance-content .label { font-size: 0.9rem; opacity: 0.8; font-weight: 400; }
.balance-content .amount { font-size: 3rem; font-weight: 700; margin: 0.5rem 0; letter-spacing: -1px; }

.status-pill {
    display: inline-flex;
    align-items: center;
    background: rgba(255,255,255,0.15);
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
}

.pulse-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    margin-right: 8px;
    box-shadow: 0 0 0 rgba(16, 185, 129, 0.4);
    animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.balance-icon {
    position: absolute;
    right: -20px;
    bottom: -20px;
    font-size: 8rem;
    opacity: 0.1;
    transform: rotate(-15deg);
}

/* Stat Box */
.stat-box, .support-card {
    background: white;
    padding: 1.5rem;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 1.2rem;
    height: 100%;
    box-shadow: var(--card-shadow);
    transition: all 0.3s ease;
}

.stat-box:hover, .support-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.icon-circle {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.bg-success-soft { background: #ecfdf5; color: #10b981; }
.bg-primary-soft { background: #eef2ff; color: #6366f1; }
.bg-info-soft { background: #f0f9ff; color: #0ea5e9; }

/* Main Card */
.main-card {
    background: white;
    border-radius: 24px;
    border: 1px solid rgba(0,0,0,0.03);
}

.card-header-premium {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #f1f5f9;
}

.header-icon {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

/* Presets */
.preset-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

@media (min-width: 768px) {
    .preset-grid { grid-template-columns: repeat(6, 1fr); }
}

.preset-item { cursor: pointer; }
.preset-box {
    background: #f8fafc;
    border: 2px solid transparent;
    padding: 1.2rem 0.5rem;
    border-radius: 16px;
    text-align: center;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
}

.preset-item:hover .preset-box { background: #f1f5f9; transform: translateY(-3px); }
.preset-item.active .preset-box {
    background: #eef2ff;
    border-color: #6366f1;
    color: #6366f1;
}

.p-amount { font-weight: 700; font-size: 1.1rem; }
.p-currency { font-size: 0.7rem; opacity: 0.6; text-transform: uppercase; }

/* Form Premium */
.input-group-premium {
    background: #f8fafc;
    border-radius: 16px;
    padding: 0.5rem 1.2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.input-group-premium:focus-within {
    background: white;
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.form-control-premium {
    background: transparent;
    border: none;
    padding: 0.8rem 0;
    font-weight: 600;
    font-size: 1.1rem;
    width: 100%;
}

.form-control-premium:focus { outline: none; }

.btn-premium {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: white;
    border: none;
    padding: 1.2rem;
    border-radius: 16px;
    font-weight: 700;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
}

.btn-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.4);
}

/* QR Area */
.qr-result-area {
    background: #f8fafc;
    border-radius: 20px;
    padding: 2rem;
    border: 1px dashed #cbd5e1;
}

.qr-card {
    background: white;
    padding: 1.2rem;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    display: inline-block;
}

.qr-frame {
    position: relative;
    overflow: hidden;
    border-radius: 12px;
}

.qr-scan-line {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: rgba(99, 102, 241, 0.5);
    box-shadow: 0 0 15px #6366f1;
    animation: scan 3s linear infinite;
}

@keyframes scan {
    0% { top: 0%; }
    50% { top: 100%; }
    100% { top: 0%; }
}

.detail-item label { font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 0.4rem; }
.value-row {
    background: white;
    padding: 1rem 1.2rem;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.btn-copy {
    background: #f1f5f9;
    border: none;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    color: #64748b;
    transition: all 0.2s ease;
}

.btn-copy:hover { background: #6366f1; color: white; }

.loading-status-box {
    background: white;
    border-radius: 16px;
    padding: 1.2rem;
    display: flex;
    align-items: center;
    gap: 1.2rem;
    border: 1px solid #e2e8f0;
}

.pulse-loader-main {
    width: 16px;
    height: 16px;
    background: #6366f1;
    border-radius: 50%;
    position: relative;
}

.pulse-loader-main::after {
    content: "";
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    border-radius: 50%;
    background: inherit;
    animation: pulse-main 2s infinite;
}

@keyframes pulse-main {
    0% { transform: scale(1); opacity: 0.8; }
    100% { transform: scale(4); opacity: 0; }
}

/* Table Premium */
.table-premium thead th {
    background: #f8fafc;
    padding: 1.2rem 1rem;
    font-size: 0.75rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 0.5px;
}

.table-premium tbody td { padding: 1.2rem 1rem; vertical-align: middle; }

.amount-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.9rem;
}
.amount-badge.plus { background: #ecfdf5; color: #10b981; }
.amount-badge.minus { background: #fef2f2; color: #ef4444; }

.badge-mini { font-size: 0.65rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px; text-transform: uppercase; width: fit-content; margin-top: 4px; }
.badge-success { background: #ecfdf5; color: #10b981; }
.badge-warning { background: #fffbeb; color: #f59e0b; }
.badge-danger { background: #fef2f2; color: #ef4444; }

/* Bank Info */
.bank-info-list { display: flex; flex-direction: column; gap: 1.5rem; }
.info-item .label { font-size: 0.8rem; color: #64748b; margin-bottom: 0.3rem; display: block; }

.promo-card {
    background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
    border-radius: 24px;
    padding: 2rem;
    position: relative;
    overflow: hidden;
    color: white;
}

.promo-content { position: relative; z-index: 1; }
.promo-icon { font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.9; }

/* Alert Premium */
.alert-premium {
    padding: 1rem;
    border-radius: 14px;
    display: flex;
    gap: 0.8rem;
    font-size: 0.85rem;
    line-height: 1.4;
}
.alert-premium.success { background: #ecfdf5; color: #065f46; }

@media (max-width: 576px) {
    .balance-card { padding: 1.5rem; }
    .amount { font-size: 2rem !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const presets = document.querySelectorAll('.preset-item');
    const amountInput = document.getElementById('depositAmount');
    const generateBtn = document.getElementById('generateQrBtn');
    const qrResult = document.getElementById('walletQrResult');
    const qrImage = document.getElementById('generatedQrImage');
    const amountText = document.getElementById('generatedAmountText');
    const contentDisplay = document.getElementById('generatedTransferContent');
    const copyBtn = document.getElementById('copyContentBtn');
    
    let currentTransferContent = '';
    let pollingInterval = null;

    function formatMoney(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
    }

    presets.forEach(item => {
        item.addEventListener('click', () => {
            presets.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            amountInput.value = item.dataset.amount;
        });
    });

    amountInput.addEventListener('input', () => {
        presets.forEach(i => i.classList.remove('active'));
    });

    generateBtn.addEventListener('click', async () => {
        const amount = parseInt(amountInput.value);
        
        if (pollingInterval) clearInterval(pollingInterval);

        if (isNaN(amount) || amount < 50000 || amount > 10000000) {
            Swal.fire({
                icon: 'warning',
                title: 'Số tiền không hợp lệ',
                text: 'Vui lòng nhập từ 50.000đ đến 10.000.000đ',
                background: '#fff',
                confirmButtonColor: '#6366f1'
            });
            return;
        }

        generateBtn.disabled = true;
        generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> ĐANG XỬ LÝ...';

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
                
                // Luôn hiện trạng thái chờ thanh toán tự động
                document.getElementById('autoLoadingStatus').classList.remove('d-none');
                const alertBox = document.getElementById('depositInfoBox');
                alertBox.innerHTML = '<div class="alert-icon"><i class="fas fa-magic"></i></div><div class="alert-text">Hệ thống sẽ tự động cộng tiền sau khi bạn chuyển khoản thành công.</div>';
                alertBox.className = 'alert-premium success mt-2';
                
                startPolling(result.transfer_code);

                qrResult.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Bắt đầu đếm ngược 60s
                startCooldown(60);
            } else {
                Swal.fire('Lỗi', result.message || 'Không thể tạo yêu cầu', 'error');
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<span class="btn-text">TẠO MÃ QR THANH TOÁN</span><i class="fas fa-qrcode ms-2"></i>';
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Lỗi', 'Không thể kết nối máy chủ', 'error');
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<span class="btn-text">TẠO MÃ QR THANH TOÁN</span><i class="fas fa-qrcode ms-2"></i>';
        }
    });

    function startCooldown(seconds) {
        let timeLeft = seconds;
        generateBtn.disabled = true;
        
        const timer = setInterval(() => {
            timeLeft--;
            generateBtn.innerHTML = `<span class="btn-text">VUI LÒNG CHỜ ${timeLeft}S...</span>`;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<span class="btn-text">TẠO MÃ QR THANH TOÁN</span><i class="fas fa-qrcode ms-2"></i>';
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
                    Swal.fire({
                        title: 'Thành Công!',
                        text: 'Tiền đã được cộng vào tài khoản của bạn.',
                        icon: 'success',
                        background: '#fff',
                        confirmButtonColor: '#10b981',
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

    window.copyText = function(text) {
        navigator.clipboard.writeText(text).then(() => {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: 'Đã sao chép nội dung'
            });
        });
    };

    document.querySelectorAll('.btn-copy').forEach(btn => {
        btn.addEventListener('click', () => {
            const text = btn.dataset.copy || btn.previousElementSibling?.textContent;
            if(text) copyText(text);
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
