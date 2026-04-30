<?php ob_start(); ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="mb-0">Ví tiền của tôi</h2>
        <div>
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#depositModal">
                <i class="fas fa-plus-circle"></i> Nạp tiền
            </button>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deactivateModal">
                <i class="fas fa-user-times"></i> Hủy tài khoản Seller
            </button>
        </div>
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

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-success shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title mb-1"><i class="fas fa-wallet"></i> Số dư khả dụng</h6>
                <h2 class="mb-0"><?= money($wallet['balance'] ?? 0) ?></h2>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?= url('/seller/withdrawals') ?>" class="btn btn-light btn-sm w-100 fw-bold text-success">Rút tiền</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title mb-1"><i class="fas fa-hourglass-half"></i> Tiền đang giữ</h6>
                <h2 class="mb-0"><?= money($wallet['held_balance'] ?? 0) ?></h2>
                <small class="d-block mt-1" style="font-size: 0.75rem;">Sẽ được chuyển sau 7 ngày</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title mb-1"><i class="fas fa-shield-alt"></i> Tiền cọc</h6>
                <h2 class="mb-0"><?= money($wallet['deposit_balance'] ?? 0) ?></h2>
                <small class="d-block mt-1" style="font-size: 0.75rem;">Đảm bảo giao dịch</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-primary shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title mb-1"><i class="fas fa-chart-line"></i> Tổng thu nhập</h6>
                <h2 class="mb-0"><?= money($wallet['total_earned'] ?? 0) ?></h2>
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
                        <th>Mã GD</th>
                        <th>Loại giao dịch</th>
                        <th>Số tiền</th>
                        <th>Số dư sau</th>
                        <th>Mô tả</th>
                        <th>Thời gian</th>
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
                                <td>#<?= $tx['id'] ?></td>
                                <td>
                                    <?php
                                    $typeLabels = [
                                        'deposit' => ['success', 'Nạp tiền'],
                                        'purchase' => ['danger', 'Mua hàng'],
                                        'sale_income' => ['success', 'Bán hàng'],
                                        'admin_fee' => ['danger', 'Phí nền tảng'],
                                        'withdrawal' => ['warning', 'Rút tiền'],
                                        'withdrawal_fee' => ['danger', 'Phí rút tiền'],
                                        'refund' => ['info', 'Hoàn tiền'],
                                        'affiliate_commission' => ['success', 'Hoa hồng']
                                    ];
                                    $label = $typeLabels[$tx['type']] ?? ['secondary', $tx['type']];
                                    ?>
                                    <span class="badge bg-<?= $label[0] ?>"><?= $label[1] ?></span>
                                </td>
                                <td>
                                    <?php if (in_array($tx['type'], ['purchase', 'admin_fee', 'withdrawal', 'withdrawal_fee'])): ?>
                                        <span class="text-danger fw-bold">-<?= money($tx['amount']) ?></span>
                                    <?php else: ?>
                                        <span class="text-success fw-bold">+<?= money($tx['amount']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= money($tx['balance_after']) ?></td>
                                <td><small class="text-muted"><?= e($tx['description']) ?></small></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($tx['created_at'])) ?></td>
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
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-wallet"></i> Nạp tiền vào ví</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/seller/wallet/deposit') ?>" method="POST" id="depositForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Chọn số tiền cần nạp. Admin sẽ xác nhận sau khi bạn chuyển khoản.
                        <hr class="my-2 opacity-50">
                        <small>
                            <i class="fas fa-clock"></i> <strong>Giới hạn:</strong> Tối đa <strong>5 lần</strong> nạp mỗi <strong>2 tiếng</strong>.<br>
                            <i class="fas fa-exclamation-triangle text-warning"></i> Không tạo nhiều yêu cầu cho cùng 1 lần chuyển khoản. Mỗi yêu cầu tương ứng 1 lần chuyển khoản thực tế.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Số tiền nạp <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <input type="number" name="amount" class="form-control" placeholder="Nhập số tiền" min="100000" step="1000" required>
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <small class="text-danger fw-bold">⚠️ Tối thiểu: 100,000 VNĐ. Không chấp nhận nạp dưới 100k.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn nhanh:</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="setAmount(100000)">100,000 đ</button>
                            <button type="button" class="btn btn-outline-primary" onclick="setAmount(500000)">500,000 đ</button>
                            <button type="button" class="btn btn-outline-primary" onclick="setAmount(1000000)">1,000,000 đ</button>
                            <button type="button" class="btn btn-outline-primary" onclick="setAmount(5000000)">5,000,000 đ</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ghi chú (tùy chọn)</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Ví dụ: Nạp tiền để trả cọc stock"></textarea>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <strong><i class="fas fa-university"></i> Thông tin chuyển khoản:</strong>
                        <hr class="my-2">
                        <div>
                            Ngân hàng: <strong>MB Bank</strong><br>
                            STK: <strong class="text-primary fs-6">0783704196</strong><br>
                            Chủ TK: <strong>TRAN THANH TUAN</strong><br>
                            Nội dung: <strong class="text-danger">NAPSELLER</strong>
                        </div>
                        <hr class="my-2">
                        <small class="text-muted"><i class="fas fa-info-circle"></i> Sau khi chuyển khoản, admin sẽ xác nhận và cộng tiền vào ví của bạn.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Gửi yêu cầu nạp tiền</button>
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
function setAmount(amount) {
    document.querySelector('input[name="amount"]').value = amount;
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Ví tiền';
require_once __DIR__ . '/../layouts/seller.php';
?>
