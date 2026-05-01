<?php 
ob_start();
?>

<div class="container-fluid py-4">
    <!-- Financial Header -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary-subtle text-primary rounded-circle p-2 me-3">
                            <i class="fas fa-wallet fa-lg"></i>
                        </div>
                        <h6 class="mb-0 text-muted">Tổng số dư ví</h6>
                    </div>
                    <h3 class="mb-0 fw-bold text-dark"><?= money($wallet['balance']) ?></h3>
                    <div class="mt-2 small text-muted">
                        Bao gồm cả tiền cọc bảo chứng.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <?php 
                $riskAmount = ($disputeAmount ?? 0) + ($warrantyAmount ?? 0);
                $heldAmount = max($minBalance, $riskAmount);
                $isHeldByRisk = $riskAmount > $minBalance;
            ?>
            <div class="card border-0 shadow-sm rounded-4 h-100 <?= $isHeldByRisk ? 'bg-warning-subtle' : 'bg-white' ?>">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="<?= $isHeldByRisk ? 'bg-warning text-dark' : 'bg-danger-subtle text-danger' ?> rounded-circle p-2 me-3">
                            <i class="fas <?= $isHeldByRisk ? 'fa-exclamation-triangle' : 'fa-lock' ?> fa-lg"></i>
                        </div>
                        <h6 class="mb-0 text-muted"><?= $isHeldByRisk ? 'Tiền bị giữ (Khiếu nại/Bảo hành)' : 'Quỹ bảo chứng' ?></h6>
                    </div>
                    <h3 class="mb-0 fw-bold <?= $isHeldByRisk ? 'text-warning-emphasis' : 'text-danger' ?>"><?= money($heldAmount) ?></h3>
                    <div class="mt-2 small text-muted">
                        <?= $isHeldByRisk ? 'Giữ lại tương ứng giá trị đơn khiếu nại & bảo hành.' : 'Khoản tiền giữ lại cố định.' ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-gradient-success text-white position-relative overflow-hidden">
                <div class="card-body p-4 position-relative z-1">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fas fa-hand-holding-usd fa-lg"></i>
                        </div>
                        <h6 class="mb-0 text-white-50">Tiền có thể rút</h6>
                    </div>
                    <h3 class="mb-0 fw-bold"><?= money(max(0, $wallet['balance'] - $heldAmount)) ?></h3>
                    <div class="mt-2 small text-white-50">
                        Số tiền bạn có thể tạo lệnh rút.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-info-subtle text-info rounded-circle p-2 me-3">
                            <i class="fas fa-history fa-lg"></i>
                        </div>
                        <h6 class="mb-0 text-muted">Tổng đã rút</h6>
                    </div>
                    <h3 class="mb-0 fw-bold text-dark"><?= money($wallet['total_withdrawn']) ?></h3>
                    <div class="mt-2 small text-muted">
                        Số tiền đã về túi thành công.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Withdrawal Form -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-paper-plane me-2 text-primary"></i>Yêu cầu rút tiền</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= url('/seller/withdrawals/store') ?>" method="POST" id="withdrawForm">
                        <?= csrf_field() ?>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-muted">Số tiền rút</label>
                            <?php 
                                $maxWithdrawable = max(0, $wallet['balance'] - $heldAmount);
                                $canWithdraw = $maxWithdrawable >= $minAmount;
                            ?>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">VNĐ</span>
                                <input type="number" name="amount" id="withdrawAmount" class="form-control bg-light border-start-0 fw-bold" 
                                       required min="<?= $minAmount ?>" max="<?= $maxWithdrawable ?>" step="1000" placeholder="0" 
                                       <?= !$canWithdraw ? 'disabled' : '' ?>>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">Tối thiểu: <span class="fw-bold"><?= number_format($minAmount) ?>đ</span></small>
                                <?php if ($canWithdraw): ?>
                                    <small class="text-primary cursor-pointer fw-bold" onclick="setMaxAmount()">Rút tối đa</small>
                                <?php else: ?>
                                    <small class="text-danger fw-bold">Chưa đủ số dư</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="alert alert-warning border-0 rounded-4 p-3 mb-4">
                            <div class="d-flex">
                                <i class="fas fa-shield-alt mt-1 me-3"></i>
                                <div class="small">
                                    <strong>Quỹ bảo chứng:</strong> Bạn cần giữ lại ít nhất <strong><?= number_format($minBalance) ?>đ</strong> trong ví để duy trì hoạt động bán hàng.
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-muted">Phương thức nhận</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="method" id="method_bank" value="bank" checked>
                                    <label class="btn btn-outline-light text-dark w-100 py-3 rounded-3 shadow-none border" for="method_bank">
                                        <i class="fas fa-university d-block mb-1 fa-lg text-primary"></i>
                                        <span class="small">Bank</span>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="method" id="method_momo" value="momo">
                                    <label class="btn btn-outline-light text-dark w-100 py-3 rounded-3 shadow-none border" for="method_momo">
                                        <i class="fas fa-wallet d-block mb-1 fa-lg text-danger"></i>
                                        <span class="small">Momo</span>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="method" id="method_usdt" value="usdt">
                                    <label class="btn btn-outline-light text-dark w-100 py-3 rounded-3 shadow-none border" for="method_usdt">
                                        <i class="fas fa-coins d-block mb-1 fa-lg text-success"></i>
                                        <span class="small">USDT</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-muted">Thông tin tài khoản</label>
                            <textarea name="account_info" class="form-control bg-light border-0" rows="4" required 
                                      placeholder="Ví dụ: STK: 123456 - VCB - NGUYEN VAN A"></textarea>
                        </div>
                        
                        <!-- Fee Calculation -->
                        <div class="bg-light rounded-4 p-3 mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Phí dịch vụ (<?= $feePercent ?>%)</span>
                                <span class="text-danger fw-bold" id="feeDisplay">- 0đ</span>
                            </div>
                            <div class="d-flex justify-content-between pt-2 border-top border-secondary border-opacity-10">
                                <span class="fw-bold text-dark">Thực nhận</span>
                                <span class="fw-bold text-success fs-5" id="netAmountDisplay">0đ</span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm" <?= !$canWithdraw ? 'disabled' : '' ?>>
                            <i class="fas fa-check-circle me-2"></i>XÁC NHẬN RÚT TIỀN
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Withdrawal History -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Lịch sử giao dịch</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-uppercase small fw-bold">
                                    <th class="ps-4 py-3">Ngày yêu cầu</th>
                                    <th>Số tiền rút</th>
                                    <th>Phí & Thực nhận</th>
                                    <th>Phương thức</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($withdrawals)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-receipt fa-3x mb-3 opacity-25"></i>
                                            <p>Bạn chưa có yêu cầu rút tiền nào.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($withdrawals as $w): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark"><?= date('d/m/Y', strtotime($w['created_at'])) ?></div>
                                            <div class="small text-muted"><?= date('H:i', strtotime($w['created_at'])) ?></div>
                                        </td>
                                        <td>
                                            <span class="fw-bold fs-6 text-dark"><?= number_format($w['amount']) ?>đ</span>
                                        </td>
                                        <td>
                                            <div class="small text-danger">-<?= number_format($w['fee_amount']) ?>đ (Phí)</div>
                                            <div class="fw-bold text-success fs-6"><?= number_format($w['receive_amount']) ?>đ</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark text-uppercase px-3 py-2 border rounded-pill">
                                                <?php if($w['method'] == 'bank') echo '<i class="fas fa-university me-1 text-primary"></i>Bank'; 
                                                      elseif($w['method'] == 'momo') echo '<i class="fas fa-wallet me-1 text-danger"></i>Momo';
                                                      else echo '<i class="fas fa-coins me-1 text-success"></i>USDT'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($w['status'] === 'pending'): ?>
                                                <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">
                                                    <i class="fas fa-clock me-1"></i> Chờ duyệt
                                                </span>
                                            <?php elseif ($w['status'] === 'approved'): ?>
                                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                                    <i class="fas fa-check-circle me-1"></i> Thành công
                                                </span>
                                            <?php elseif ($w['status'] === 'rejected'): ?>
                                                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">
                                                    <i class="fas fa-times-circle me-1"></i> Từ chối
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($w['admin_note']): ?>
                                                <div class="mt-1 small text-muted text-truncate" style="max-width: 150px;" title="<?= e($w['admin_note']) ?>">
                                                    <i class="fas fa-comment-dots"></i> <?= e($w['admin_note']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const feePercent = <?= $feePercent ?>;
const withdrawInput = document.getElementById('withdrawAmount');
const feeDisplay = document.getElementById('feeDisplay');
const netAmountDisplay = document.getElementById('netAmountDisplay');

function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
}

function updateCalculation() {
    const amount = parseFloat(withdrawInput.value) || 0;
    const fee = Math.floor(amount * (feePercent / 100));
    const net = amount - fee;
    
    feeDisplay.textContent = '- ' + formatMoney(fee);
    netAmountDisplay.textContent = formatMoney(net);
}

function setMaxAmount() {
    const maxWithdrawable = <?= max(0, $wallet['balance'] - $heldAmount) ?>;
    if (maxWithdrawable < <?= $minAmount ?>) {
        alert('Số dư khả dụng sau khi trừ quỹ bảo chứng không đủ mức rút tối thiểu.');
        return;
    }
    withdrawInput.value = maxWithdrawable;
    updateCalculation();
}

withdrawInput.addEventListener('input', updateCalculation);
</script>

<style>
.bg-gradient-success {
    background: linear-gradient(45deg, #1cc88a 0%, #13855c 100%);
}
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }

.cursor-pointer { cursor: pointer; }

.btn-check:checked + .btn-outline-light {
    background-color: #f8f9fa !important;
    border-color: #4e73df !important;
    box-shadow: 0 0 0 2px rgba(78, 115, 223, 0.2) !important;
}

.card { transition: transform 0.2s; }
.card:hover { transform: translateY(-2px); }

.table thead th {
    border-top: none;
    font-size: 11px;
    color: #6c757d;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý Rút tiền';
require_once __DIR__ . '/../layouts/seller.php';
?>
