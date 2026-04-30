<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-users"></i> Affiliate Dashboard</h2>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h6><i class="fas fa-mouse-pointer"></i> Tổng click</h6>
                    <h2><?= $stats['total_clicks'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6><i class="fas fa-user-plus"></i> Người giới thiệu</h6>
                    <h2><?= $stats['total_referrals'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h6><i class="fas fa-money-bill-wave"></i> Tổng hoa hồng</h6>
                    <h2><?= money($stats['total_commissions']) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h6><i class="fas fa-clock"></i> Chờ thanh toán</h6>
                    <h2><?= money($stats['pending_commissions']) ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-link"></i> Link giới thiệu của bạn</h5>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control" value="<?= e($affiliateLink) ?>" id="affiliateLink" readonly>
                        <button class="btn btn-success" onclick="copyLink()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <small class="text-muted">Mã giới thiệu: <strong><?= e($referralCode) ?></strong></small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-history"></i> Lịch sử hoa hồng</h5>
        </div>
        <div class="card-body">
            <?php if (empty($recentCommissions)): ?>
                <p class="text-muted">Chưa có hoa hồng nào</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Người được giới thiệu</th>
                            <th>Đơn hàng</th>
                            <th>Hoa hồng</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentCommissions as $comm): ?>
                        <tr>
                            <td><?= Helper::formatDate($comm['created_at']) ?></td>
                            <td><?= e($comm['referred_user_name'] ?? 'N/A') ?></td>
                            <td><?= e($comm['order_code'] ?? 'N/A') ?></td>
                            <td class="text-success fw-bold"><?= money($comm['amount']) ?></td>
                            <td>
                                <?php if ($comm['status'] === 'paid'): ?>
                                    <span class="badge bg-success">Đã thanh toán</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Chờ thanh toán</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function copyLink() {
    const input = document.getElementById('affiliateLink');
    input.select();
    document.execCommand('copy');
    alert('Đã copy link giới thiệu!');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
