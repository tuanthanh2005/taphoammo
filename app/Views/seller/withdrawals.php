<?php 
ob_start();
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h6><i class="fas fa-wallet"></i> Số dư khả dụng</h6>
                <h2><?= money($wallet['balance']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h6><i class="fas fa-chart-line"></i> Tổng đã kiếm</h6>
                <h2><?= money($wallet['total_earned']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h6><i class="fas fa-money-bill-wave"></i> Đã rút</h6>
                <h2><?= money($wallet['total_withdrawn']) ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-plus"></i> Tạo yêu cầu rút tiền</h5>
            </div>
            <div class="card-body">
                <form action="<?= url('/seller/withdrawals/store') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Số tiền muốn rút</label>
                        <input type="number" name="amount" class="form-control" required min="<?= $minAmount ?>" step="1000">
                        <small class="text-muted">Tối thiểu: <?= money($minAmount) ?></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phương thức nhận</label>
                        <select name="method" class="form-select" required>
                            <option value="">-- Chọn --</option>
                            <option value="bank">Chuyển khoản ngân hàng</option>
                            <option value="momo">Ví Momo</option>
                            <option value="usdt">USDT (TRC20)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Thông tin nhận tiền</label>
                        <textarea name="account_info" class="form-control" rows="4" required placeholder="Nhập số tài khoản, tên chủ tài khoản, ngân hàng..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <small>
                            <i class="fas fa-info-circle"></i> 
                            Phí rút tiền: <strong><?= $feePercent ?>%</strong><br>
                            Ví dụ: Rút 100,000đ → Nhận <?= money(100000 * (100 - $feePercent) / 100) ?>
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-paper-plane"></i> Gửi yêu cầu
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Lịch sử rút tiền</h5>
            </div>
            <div class="card-body">
                <?php if (empty($withdrawals)): ?>
                    <p class="text-muted">Chưa có yêu cầu rút tiền nào</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Số tiền</th>
                                <th>Phí</th>
                                <th>Thực nhận</th>
                                <th>Phương thức</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($withdrawals as $w): ?>
                            <tr>
                                <td><?= Helper::formatDate($w['created_at']) ?></td>
                                <td><?= money($w['amount']) ?></td>
                                <td class="text-danger">-<?= money($w['fee_amount']) ?></td>
                                <td class="fw-bold text-success"><?= money($w['receive_amount']) ?></td>
                                <td><?= e($w['method']) ?></td>
                                <td>
                                    <?php if ($w['status'] === 'pending'): ?>
                                        <span class="badge bg-warning">Chờ duyệt</span>
                                    <?php elseif ($w['status'] === 'approved'): ?>
                                        <span class="badge bg-success">Đã duyệt</span>
                                    <?php elseif ($w['status'] === 'rejected'): ?>
                                        <span class="badge bg-danger">Từ chối</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($w['admin_note']): ?>
                            <tr>
                                <td colspan="6" class="bg-light">
                                    <small><i class="fas fa-comment"></i> Ghi chú: <?= e($w['admin_note']) ?></small>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Rút tiền';
require_once __DIR__ . '/../layouts/seller.php';
?>
