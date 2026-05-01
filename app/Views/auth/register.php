<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-page py-5" style="background: #f8fafc; min-height: calc(100vh - 200px); display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-content-center g-4">
            <!-- Registration Form -->
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 60px; height: 60px; font-size: 24px;">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h3 class="fw-bold mb-1">Tạo tài khoản</h3>
                            <p class="text-muted small">Tham gia cộng đồng AI CỦA TÔI ngay hôm nay</p>
                        </div>
                        
                        <form action="<?= url('/register') ?>" method="POST">
                            <?= csrf_field() ?>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Họ tên</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="far fa-user text-muted"></i></span>
                                        <input type="text" name="name" class="form-control border-0 bg-light" required value="<?= e(old('name')) ?>" placeholder="VD: Trần Văn A">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-at text-muted"></i></span>
                                        <input type="text" name="username" class="form-control border-0 bg-light" required value="<?= e(old('username')) ?>" placeholder="username">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="far fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control border-0 bg-light" required value="<?= e(old('email')) ?>" placeholder="example@email.com">
                                </div>
                            </div>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Mật khẩu</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-muted"></i></span>
                                        <input type="password" name="password" class="form-control border-0 bg-light" required minlength="6" placeholder="••••••••">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Xác nhận</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-check-double text-muted"></i></span>
                                        <input type="password" name="confirm_password" class="form-control border-0 bg-light" required placeholder="••••••••">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Mã giới thiệu (nếu có)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-gift text-muted"></i></span>
                                    <input type="text" name="referral_code" class="form-control border-0 bg-light" value="<?= e($_GET['ref'] ?? '') ?>" placeholder="Nhập mã ref">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-3 shadow-sm mb-4">
                                <i class="fas fa-paper-plane me-2"></i> ĐĂNG KÝ NGAY
                            </button>
                        </form>
                        
                        <div class="position-relative mb-4 text-center">
                            <hr class="text-muted opacity-25">
                            <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">hoặc tiếp tục với</span>
                        </div>
                        
                        <a href="<?= url('/auth/google') ?>" class="btn btn-outline-danger w-100 py-2 rounded-3 mb-4 shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18" height="18">
                            <span class="fw-semibold">Đăng ký bằng Google</span>
                        </a>
                        
                        <div class="text-center">
                            <p class="mb-0 text-muted small">Đã có tài khoản? <a href="<?= url('/login') ?>" class="text-success fw-bold text-decoration-none">Đăng nhập ngay</a></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rules Section -->
            <div class="col-lg-4 d-none d-lg-block">
                <div class="card border-0 shadow-lg bg-dark text-white h-100" style="border-radius: 20px;">
                    <div class="card-body p-5">
                        <h4 class="fw-bold mb-4 d-flex align-items-center text-warning">
                            <i class="fas fa-info-circle me-2"></i> Lưu ý đăng ký
                        </h4>
                        
                        <div class="rule-item d-flex gap-3 mb-4">
                            <div class="flex-shrink-0">
                                <span class="badge bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">1</span>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Thông tin chính xác</h6>
                                <p class="small text-white-50 mb-0">Vui lòng sử dụng Email thật để có thể khôi phục mật khẩu và nhận thông báo đơn hàng.</p>
                            </div>
                        </div>

                        <div class="rule-item d-flex gap-3 mb-4">
                            <div class="flex-shrink-0">
                                <span class="badge bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">2</span>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Tên người dùng</h6>
                                <p class="small text-white-50 mb-0">Username nên viết liền không dấu, dùng để đăng nhập và hiển thị trên hệ thống.</p>
                            </div>
                        </div>

                        <div class="rule-item d-flex gap-3 mb-4">
                            <div class="flex-shrink-0">
                                <span class="badge bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">3</span>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Mật khẩu an toàn</h6>
                                <p class="small text-white-50 mb-0">Sử dụng mật khẩu có độ dài ít nhất 6 ký tự, bao gồm chữ và số để đảm bảo an toàn.</p>
                            </div>
                        </div>

                        <div class="rule-item d-flex gap-3 mb-4">
                            <div class="flex-shrink-0">
                                <span class="badge bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">4</span>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Chính sách bảo mật</h6>
                                <p class="small text-white-50 mb-0">Khi đăng ký, bạn đồng ý với các điều khoản sử dụng và chính sách bảo mật của AI CỦA TÔI.</p>
                            </div>
                        </div>

                        <div class="mt-5 p-4 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10">
                            <div class="text-center">
                                <div class="mb-2 small opacity-75">Bạn cần hỗ trợ nhanh?</div>
                                <a href="https://t.me/specademy" target="_blank" class="btn btn-info btn-sm rounded-pill px-4 text-white fw-bold">
                                    <i class="fab fa-telegram me-1"></i> Chat với Admin
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
