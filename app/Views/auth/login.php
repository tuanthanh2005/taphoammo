<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4"><i class="fas fa-sign-in-alt"></i> Đăng nhập</h3>
                    
                    <form action="<?= url('/login') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Email hoặc Username</label>
                            <input type="text" name="email" class="form-control" required value="<?= old('email') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100 mb-3">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </button>
                    </form>
                    
                    <div class="text-center mb-3">
                        <span class="text-muted">hoặc</span>
                    </div>
                    
                    <a href="<?= url('/auth/google') ?>" class="btn btn-outline-danger w-100 mb-3">
                        <i class="fab fa-google"></i> Đăng nhập bằng Google
                    </a>
                    
                    <div class="text-center">
                        <p class="mb-0">Chưa có tài khoản? <a href="<?= url('/register') ?>">Đăng ký ngay</a></p>
                    </div>
                    

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
