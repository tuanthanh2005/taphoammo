<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$bankCode = trim($walletSettings['deposit_bank_code'] ?? 'mb');
$bankName = trim($walletSettings['deposit_bank_name'] ?? 'MB Bank');
$accountName = trim($walletSettings['deposit_account_name'] ?? 'TRAN THANH TUAN');
$accountNumber = trim($walletSettings['deposit_account_number'] ?? '0783704196');
$supportTelegram = trim($walletSettings['telegram_support_username'] ?? '@specademy');
$supportTelegramUrl = trim($walletSettings['telegram_support_url'] ?? 'https://t.me/specademy');
$transferContent = 'NAP' . str_pad((string) Auth::id(), 4, '0', STR_PAD_LEFT);
$depositPresets = [50000, 100000, 200000, 500000, 1000000, 2000000, 5000000];
?>

<div class="wallet-deposit-page py-5">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="wallet-summary-card h-100">
                    <div class="wallet-summary-head">
                        <div class="wallet-summary-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div>
                            <div class="wallet-summary-label">Ví tiền</div>
                            <h1 class="wallet-summary-balance mb-0"><?= money($wallet['balance'] ?? 0) ?></h1>
                        </div>
                    </div>
                    <p class="wallet-summary-text mb-4">Chọn số tiền cần nạp, tạo VietQR rồi xác nhận chuyển khoản để hệ
                        thống báo về Telegram xử lý.</p>

                    <div class="row g-3">
                        <div class="col-md-6 col-xl-4">
                            <div class="wallet-mini-stat">
                                <span>Tổng đã kiếm</span>
                                <strong><?= money($wallet['total_earned'] ?? 0) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="wallet-mini-stat">
                                <span>Nạp tối thiểu</span>
                                <strong>50.000đ</strong>
                            </div>
                        </div>
                        <div class="col-md-12 col-xl-4">
                            <div class="wallet-mini-stat">
                                <span>Nạp tối đa</span>
                                <strong>5.000.000đ</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="wallet-help-card h-100">
                    <h4>Hỗ trợ nạp tiền</h4>
                    <p class="text-muted mb-4">Mọi xác nhận nạp tiền sẽ báo qua Telegram. Nếu cần hỗ trợ thêm, liên hệ
                        trực tiếp.</p>
                    <a href="<?= e($supportTelegramUrl) ?>" target="_blank" rel="noopener"
                        class="btn btn-primary btn-lg rounded-pill w-100">
                        <i class="fab fa-telegram-plane me-2"></i><?= e($supportTelegram) ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="wallet-deposit-card">
                    <div class="wallet-section-top">
                        <div>
                            <div class="wallet-kicker">Nạp tiền</div>
                            <h3 class="mb-1">Tạo mã QR chuyển khoản</h3>
                            <p class="text-muted mb-0">Chọn một mệnh giá có sẵn hoặc nhập số tiền bạn muốn nạp.</p>
                        </div>
                    </div>

                    <div class="wallet-amount-area">
                        <label class="form-label fw-bold">Chọn mệnh giá</label>
                        <div class="wallet-amount-grid mb-4">
                            <?php foreach ($depositPresets as $preset): ?>
                                <button type="button" class="wallet-amount-btn" data-amount="<?= $preset ?>">
                                    <?= number_format($preset / 1000, 0, ',', '.') ?>K
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <label for="depositAmount" class="form-label fw-bold">Hoặc nhập số tiền</label>
                        <div class="wallet-amount-input-wrap">
                            <input type="number" id="depositAmount" class="form-control form-control-lg" min="50000"
                                max="5000000" step="1000" placeholder="Nhập số tiền từ 50.000đ đến 5.000.000đ">
                            <span>VNĐ</span>
                        </div>
                        <div class="small text-muted mt-2">Tối thiểu 50.000đ, tối đa 5.000.000đ mỗi lần tạo QR.</div>

                        <div class="d-grid gap-3 mt-4">
                            <button type="button" id="generateQrBtn" class="btn btn-success btn-lg rounded-pill">
                                <i class="fas fa-qrcode me-2"></i>Tạo QR
                            </button>
                            <button type="button" id="confirmTransferBtn"
                                class="btn btn-outline-primary btn-lg rounded-pill d-none">
                                <i class="fas fa-paper-plane me-2"></i>Xác nhận chuyển khoản
                            </button>
                        </div>
                    </div>

                    <div id="walletQrResult" class="wallet-qr-result d-none">
                        <div class="row g-4 align-items-center">
                            <div class="col-md-5">
                                <div class="wallet-qr-shell">
                                    <img id="generatedQrImage" src="" alt="VietQR" class="img-fluid">
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="wallet-transfer-info">
                                    <div class="wallet-transfer-box">
                                        <span>Số tiền</span>
                                        <strong id="generatedAmountText">0đ</strong>
                                    </div>
                                    <div class="wallet-transfer-box">
                                        <span>Nội dung chuyển khoản</span>
                                        <div class="wallet-inline-copy">
                                            <strong id="generatedTransferContent"><?= e($transferContent) ?></strong>
                                            <button type="button" class="btn btn-sm btn-light wallet-copy-btn"
                                                data-copy="<?= e($transferContent) ?>">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="wallet-transfer-note">
                                        <i class="fas fa-info-circle me-2"></i>Chuyển đúng số tiền và giữ nguyên nội
                                        dung để dễ đối soát.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="wallet-bank-card h-100">
                    <div class="wallet-section-top compact">
                        <div>
                            <div class="wallet-kicker">Thông tin nhận tiền</div>
                            <h3 class="mb-0">Tài khoản ngân hàng</h3>
                        </div>
                    </div>

                    <div class="wallet-bank-list mt-4">
                        <div class="wallet-bank-row">
                            <span>Ngân hàng</span>
                            <strong><?= e($bankName) ?></strong>
                        </div>
                        <div class="wallet-bank-row">
                            <span>Chủ tài khoản</span>
                            <strong><?= e($accountName) ?></strong>
                        </div>
                        <div class="wallet-bank-row">
                            <span>Số tài khoản</span>
                            <div class="wallet-inline-copy">
                                <strong><?= e($accountNumber) ?></strong>
                                <button type="button" class="btn btn-sm btn-light wallet-copy-btn"
                                    data-copy="<?= e($accountNumber) ?>">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="wallet-bank-row">
                            <span>Telegram hỗ trợ</span>
                            <div class="wallet-inline-copy">
                                <strong><a href="<?= e($supportTelegramUrl) ?>" target="_blank"
                                        rel="noopener"><?= e($supportTelegram) ?></a></strong>
                                <button type="button" class="btn btn-sm btn-light wallet-copy-btn"
                                    data-copy="<?= e($supportTelegram) ?>">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="wallet-section-top mb-3">
                <div>
                    <div class="wallet-kicker">Lịch sử</div>
                    <h3 class="mb-1">Giao dịch gần đây</h3>
                </div>
            </div>
        </div>

        <?php if (!empty($depositRequests)): ?>
            <div class="wallet-request-block">
                <h5 class="wallet-subtitle">Yêu cầu nạp tiền</h5>
                <div class="table-responsive">
                    <table class="table wallet-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Mã YC</th>
                                <th>Số tiền</th>
                                <th>Nội dung CK</th>
                                <th>Ngân hàng</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($depositRequests as $request): ?>
                                <tr>
                                    <td class="fw-bold text-dark">#<?= (int) $request['id'] ?></td>
                                    <td class="fw-bold text-success"><?= money($request['amount']) ?></td>
                                    <td><code
                                            class="px-2 py-1 bg-light rounded text-primary"><?= e($request['transfer_code']) ?></code>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= e($request['bank_name']) ?></div>
                                        <div class="small text-muted"><?= e($request['account_number']) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <span class="badge-status badge-soft-warning">Chờ duyệt</span>
                                        <?php elseif ($request['status'] === 'approved'): ?>
                                            <span class="badge-status badge-soft-success">Thành công</span>
                                        <?php else: ?>
                                            <span class="badge-status badge-soft-danger">Từ chối</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="text-muted small" style="max-width: 200px;">
                                            <?= e($request['admin_note'] ?? '...') ?>
                                        </div>
                                    </td>
                                    <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($transactions) && empty($depositRequests)): ?>
            <div class="wallet-empty-state">
                <div class="wallet-empty-icon"><i class="fas fa-receipt"></i></div>
                <h5 class="mb-2">Chưa có giao dịch nào</h5>
                <p class="text-muted mb-0">Khi có nạp tiền, mua hàng hoặc hoàn tiền, lịch sử sẽ hiển thị ở đây.</p>
            </div>
        <?php else: ?>
            <?php if (!empty($transactions)): ?>
                <div class="wallet-request-block">
                    <h5 class="wallet-subtitle">Biến động số dư</h5>
                    <div class="table-responsive">
                        <table class="table wallet-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Mã GD</th>
                                    <th>Loại</th>
                                    <th>Số tiền</th>
                                    <th>Số dư trước</th>
                                    <th>Số dư sau</th>
                                    <th>Mô tả</th>
                                    <th>Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $tx): ?>
                                    <?php
                                    $typeLabels = [
                                        'deposit' => ['soft-success', 'Nạp tiền'],
                                        'purchase' => ['soft-danger', 'Mua hàng'],
                                        'sale_income' => ['soft-success', 'Thu nhập'],
                                        'admin_fee' => ['soft-secondary', 'Phí admin'],
                                        'withdrawal' => ['soft-warning', 'Rút tiền'],
                                        'withdrawal_fee' => ['soft-secondary', 'Phí rút'],
                                        'refund' => ['soft-primary', 'Hoàn tiền'],
                                        'affiliate_commission' => ['soft-primary', 'Hoa hồng']
                                    ];
                                    $label = $typeLabels[$tx['type']] ?? ['soft-secondary', $tx['type']];
                                    $isMinus = in_array($tx['type'], ['purchase', 'admin_fee', 'withdrawal', 'withdrawal_fee'], true);
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-dark">#<?= (int) $tx['id'] ?></td>
                                        <td><span class="badge-status badge-<?= $label[0] ?>"><?= e($label[1]) ?></span></td>
                                        <td>
                                            <span class="fw-bold <?= $isMinus ? 'text-danger' : 'text-success' ?>">
                                                <?= $isMinus ? '-' : '+' ?>             <?= money($tx['amount']) ?>
                                            </span>
                                        </td>
                                        <td class="text-muted"><?= money($tx['balance_before']) ?></td>
                                        <td class="fw-bold text-dark"><?= money($tx['balance_after']) ?></td>
                                        <td>
                                            <div class="text-muted small" style="max-width: 250px;"><?= e($tx['description']) ?>
                                            </div>
                                        </td>
                                        <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .wallet-deposit-page {
        background:
            radial-gradient(circle at top left, rgba(139, 92, 246, 0.10), transparent 28%),
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.08), transparent 24%),
            #f8fafc;
    }

    .wallet-summary-card,
    .wallet-help-card,
    .wallet-deposit-card,
    .wallet-bank-card,
    .wallet-history-card {
        background: #fff;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
    }

    .wallet-summary-card,
    .wallet-help-card,
    .wallet-deposit-card,
    .wallet-bank-card,
    .wallet-history-card {
        padding: 28px;
    }

    .wallet-summary-card {
        background: linear-gradient(135deg, #ffffff 0%, #f7f5ff 100%);
    }

    .wallet-summary-head {
        display: flex;
        align-items: center;
        gap: 18px;
        margin-bottom: 16px;
    }

    .wallet-summary-icon,
    .wallet-help-icon {
        width: 72px;
        height: 72px;
        border-radius: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 30px;
    }

    .wallet-summary-icon {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        box-shadow: 0 14px 30px rgba(124, 58, 237, 0.24);
    }

    .wallet-help-icon {
        background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
        box-shadow: 0 14px 30px rgba(14, 165, 233, 0.24);
    }

    .wallet-summary-label,
    .wallet-kicker {
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.78rem;
        font-weight: 700;
        color: #8b5cf6;
    }

    .wallet-summary-balance {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 800;
        color: #0f172a;
    }

    .wallet-summary-text {
        color: #64748b;
    }

    .wallet-mini-stat {
        height: 100%;
        padding: 18px 20px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid #ece7ff;
    }

    .wallet-mini-stat span {
        display: block;
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 6px;
    }

    .wallet-mini-stat strong {
        color: #0f172a;
        font-size: 1.12rem;
    }

    .wallet-help-card h4,
    .wallet-section-top h3 {
        font-weight: 800;
        color: #0f172a;
    }

    .wallet-section-top {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: start;
    }

    .wallet-amount-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .wallet-amount-btn {
        border: 1px solid #e6e9f2;
        background: #fff;
        border-radius: 16px;
        padding: 16px 14px;
        font-weight: 800;
        color: #0f172a;
        transition: all 0.2s ease;
    }

    .wallet-amount-btn:hover,
    .wallet-amount-btn.active {
        border-color: #8b5cf6;
        color: #8b5cf6;
        background: #faf7ff;
    }

    .wallet-amount-input-wrap {
        position: relative;
    }

    .wallet-amount-input-wrap span {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-weight: 700;
    }

    .wallet-amount-input-wrap .form-control {
        padding-right: 72px;
        border-radius: 18px;
    }

    .wallet-qr-result {
        margin-top: 28px;
        padding-top: 28px;
        border-top: 1px solid #eef2f7;
    }

    .wallet-qr-shell {
        padding: 18px;
        border-radius: 24px;
        background: linear-gradient(180deg, #fbfcff 0%, #f4f7fb 100%);
        border: 1px solid #eceff5;
    }

    .wallet-qr-shell img {
        width: 100%;
        border-radius: 16px;
        background: #fff;
    }

    .wallet-transfer-info {
        display: grid;
        gap: 14px;
    }

    .wallet-transfer-box {
        padding: 16px 18px;
        border-radius: 18px;
        background: #faf8ff;
        border: 1px solid #ece7ff;
    }

    .wallet-transfer-box span,
    .wallet-bank-row span {
        display: block;
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 6px;
    }

    .wallet-transfer-box strong,
    .wallet-bank-row strong,
    .wallet-bank-row a {
        color: #0f172a;
        text-decoration: none;
        font-size: 1.05rem;
    }

    .wallet-transfer-note {
        border-radius: 16px;
        padding: 14px 16px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
    }

    .wallet-bank-list {
        display: grid;
        gap: 12px;
    }

    .wallet-bank-row {
        padding: 16px 18px;
        border-radius: 18px;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border: 1px solid #edf2f7;
    }

    .wallet-inline-copy {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .wallet-copy-btn {
        border-radius: 12px;
    }



    .wallet-request-block {
        background: #fff;
        padding: 24px 28px;
        margin-bottom: 24px;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
    }

    .wallet-subtitle {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .wallet-subtitle::before {
        content: '';
        display: block;
        width: 6px;
        height: 22px;
        background: linear-gradient(to bottom, #8b5cf6, #7c3aed);
        border-radius: 10px;
    }

    .wallet-table {
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .wallet-table thead th {
        background: #f8fafc !important;
        border: none !important;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 14px 20px;
    }

    .wallet-table tbody tr {
        transition: transform 0.2s ease, background-color 0.2s ease;
    }

    .wallet-table tbody tr:hover {
        background-color: #fbfbfe !important;
    }

    .wallet-table tbody td {
        border: none !important;
        padding: 16px 20px;
        background: #fff;
        border-top: 1px solid #f1f5f9 !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }

    .wallet-table tbody td:first-child {
        border-left: 1px solid #f1f5f9 !important;
        border-radius: 16px 0 0 16px;
    }

    .wallet-table tbody td:last-child {
        border-right: 1px solid #f1f5f9 !important;
        border-radius: 0 16px 16px 0;
    }

    .badge-status {
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.7rem;
        letter-spacing: 0.02em;
        display: inline-block;
    }

    .badge-soft-success {
        background: #ecfdf5;
        color: #059669;
    }

    .badge-soft-warning {
        background: #fffbeb;
        color: #d97706;
    }

    .badge-soft-danger {
        background: #fef2f2;
        color: #dc2626;
    }

    .badge-soft-primary {
        background: #eff6ff;
        color: #2563eb;
    }

    .badge-soft-secondary {
        background: #f8fafc;
        color: #475569;
    }

    .badge-soft-info {
        background: #f0fdfa;
        color: #0d9488;
    }

    .wallet-empty-state {
        text-align: center;
        padding: 48px 24px 56px;
        background: #fff;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.9);
    }

    .wallet-empty-icon {
        width: 70px;
        height: 70px;
        border-radius: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        color: #6b7280;
        font-size: 28px;
        margin-bottom: 16px;
    }

    @media (max-width: 991.98px) {

        .wallet-summary-card,
        .wallet-help-card,
        .wallet-deposit-card,
        .wallet-bank-card {
            padding: 22px;
        }

        .wallet-request-block {
            padding: 20px 22px;
        }
    }

    @media (max-width: 767.98px) {
        .wallet-amount-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .wallet-inline-copy {
            flex-direction: column;
            align-items: flex-start;
        }

        .wallet-table {
            min-width: 800px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const presets = document.querySelectorAll('.wallet-amount-btn');
        const amountInput = document.getElementById('depositAmount');
        const generateBtn = document.getElementById('generateQrBtn');
        const confirmBtn = document.getElementById('confirmTransferBtn');
        const qrResult = document.getElementById('walletQrResult');
        const qrImage = document.getElementById('generatedQrImage');
        const amountText = document.getElementById('generatedAmountText');
        const transferContent = <?= json_encode($transferContent) ?>;
        const bankCode = <?= json_encode($bankCode) ?>;
        const accountNumber = <?= json_encode($accountNumber) ?>;
        const accountName = <?= json_encode($accountName) ?>;
        const supportTelegram = <?= json_encode($supportTelegram) ?>;
        const confirmUrl = <?= json_encode(url('/user/wallet/confirm-deposit')) ?>;
        const csrfToken = <?= json_encode(csrf_token()) ?>;

        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
        }

        function getAmount() {
            return parseInt(amountInput.value || '0', 10);
        }

        function validateAmount(showAlert = true) {
            const amount = getAmount();
            if (Number.isNaN(amount) || amount < 50000 || amount > 5000000) {
                if (showAlert) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Số tiền không hợp lệ',
                        text: 'Vui lòng nhập số tiền từ 50.000đ đến 5.000.000đ.'
                    });
                }
                return false;
            }
            return true;
        }

        presets.forEach(function (button) {
            button.addEventListener('click', function () {
                presets.forEach(function (item) { item.classList.remove('active'); });
                button.classList.add('active');
                amountInput.value = button.getAttribute('data-amount');
            });
        });

        amountInput.addEventListener('input', function () {
            presets.forEach(function (item) { item.classList.remove('active'); });
        });

        generateBtn.addEventListener('click', function () {
            if (!validateAmount()) {
                return;
            }

            const amount = getAmount();
            const qrUrl = `https://img.vietqr.io/image/${bankCode}-${accountNumber}-compact2.png?accountName=${encodeURIComponent(accountName)}&addInfo=${encodeURIComponent(transferContent)}&amount=${amount}`;

            qrImage.src = qrUrl;
            amountText.textContent = formatMoney(amount);
            qrResult.classList.remove('d-none');
            confirmBtn.classList.remove('d-none');
        });

        confirmBtn.addEventListener('click', async function () {
            if (!validateAmount()) {
                return;
            }

            const amount = getAmount();

            const result = await Swal.fire({
                icon: 'question',
                title: 'Bạn đã nạp chưa?',
                text: `Bạn xác nhận đã chuyển ${formatMoney(amount)} đúng không?`,
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                confirmButtonColor: '#8b5cf6'
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const payload = new URLSearchParams();
                payload.append('csrf_token', csrfToken);
                payload.append('amount', String(amount));

                const response = await fetch(confirmUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                    },
                    body: payload.toString()
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Không thể gửi xác nhận nạp tiền');
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Đã gửi yêu cầu!',
                    text: data.message,
                    confirmButtonColor: '#8b5cf6',
                    footer: supportTelegram ? `Hỗ trợ: ${supportTelegram}` : ''
                }).then(() => {
                    window.location.reload();
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Không gửi được xác nhận',
                    text: error.message || 'Vui lòng thử lại sau.'
                });
            }
        });

        document.querySelectorAll('.wallet-copy-btn').forEach(function (button) {
            button.addEventListener('click', async function () {
                const value = button.getAttribute('data-copy') || '';

                try {
                    await navigator.clipboard.writeText(value);
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã sao chép',
                        text: value,
                        timer: 1600,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Không thể sao chép',
                        text: 'Hãy thử sao chép thủ công.'
                    });
                }
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>