<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4"><i class="fas fa-user-plus"></i> Đăng ký tài khoản</h3>
                    
                    <form action="<?= url('/register') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Họ tên</label>
                            <input type="text" name="name" class="form-control" required value="<?= old('name') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required value="<?= old('username') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="<?= old('email') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                            <small class="text-muted">Tối thiểu 6 ký tự</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mã giới thiệu (nếu có)</label>
                            <input type="text" name="referral_code" class="form-control" value="<?= $_GET['ref'] ?? '' ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100 mb-3">
                            <i class="fas fa-user-plus"></i> Đăng ký
                        </button>
                    </form>
                    
                    <div class="text-center mb-3">
                        <span class="text-muted">hoặc</span>
                    </div>
                    
                    <a href="<?= url('/auth/google') ?>" class="btn btn-outline-danger w-100 mb-3">
                        <i class="fab fa-google"></i> Đăng ký bằng Google
                    </a>
                    
                    <div class="text-center">
                        <p class="mb-0">Đã có tài khoản? <a href="<?= url('/login') ?>">Đăng nhập ngay</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
