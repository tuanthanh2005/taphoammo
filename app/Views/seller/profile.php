<?php
ob_start();
$avatar = $user['avatar'] ?? '';
?>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h3 class="fw-bold mb-1">Hồ sơ Seller</h3>
            <p class="text-muted small mb-0">Cập nhật thông tin tài khoản và mật khẩu đăng nhập.</p>
        </div>
    </div>

    <form action="<?= url('/seller/profile') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 text-center">
                        <div class="profile-avatar mx-auto mb-3">
                            <?php if ($avatar): ?>
                                <img src="<?= asset($avatar) ?>" alt="<?= e($user['name']) ?>">
                            <?php else: ?>
                                <span><?= strtoupper(substr($user['name'] ?? 'S', 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        <h5 class="fw-bold mb-1"><?= e($user['name']) ?></h5>
                        <div class="text-muted small mb-3"><?= e($user['email']) ?></div>
                        <label class="btn btn-light border rounded-pill px-4">
                            <i class="fas fa-camera me-2"></i> Đổi ảnh
                            <input type="file" name="avatar" accept="image/*" hidden>
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">Thông tin liên hệ</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Họ tên</label>
                                <input type="text" name="name" class="form-control" value="<?= e($user['name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" value="<?= e($user['email'] ?? '') ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" placeholder="Nhập số điện thoại">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">Đổi mật khẩu</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Mật khẩu hiện tại</label>
                                <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Mật khẩu mới</label>
                                <input type="password" name="new_password" class="form-control" autocomplete="new-password">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Xác nhận</label>
                                <input type="password" name="confirm_password" class="form-control" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="text-muted small mt-2">Bỏ trống 3 ô mật khẩu nếu không muốn đổi mật khẩu.</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">
                        <i class="fas fa-save me-2"></i> Lưu thay đổi
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.profile-avatar {
    width: 104px;
    height: 104px;
    border-radius: 24px;
    background: linear-gradient(135deg, #7c3aed, #22c55e);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    font-size: 2.2rem;
    font-weight: 800;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

@media (max-width: 575.98px) {
    .container-fluid.py-4 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Hồ sơ Seller';
require_once __DIR__ . '/../layouts/seller.php';
?>
