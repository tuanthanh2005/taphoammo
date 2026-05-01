<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center g-4">
        <!-- Login Form -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-lg" style="border-radius: 20px;">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3 shadow-sm" style="width: 72px; height: 72px; background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);">
                            <span class="fw-bold text-white" style="font-size: 28px; letter-spacing: 1px;">AI</span>
                        </div>
                        <h3 class="fw-bold">Đăng nhập</h3>
                        <p class="text-muted small">Chào mừng bạn quay trở lại với AI CỦA TÔI</p>
                    </div>
                    
                    <form action="<?= url('/login') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase">Email hoặc Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="far fa-envelope text-muted"></i></span>
                                <input type="text" name="email" class="form-control bg-light border-start-0" placeholder="Nhập email hoặc tên người dùng" required value="<?= old('email') ?>">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold small text-uppercase mb-0">Mật khẩu</label>
                                <a href="#" class="text-decoration-none small text-primary">Quên mật khẩu?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-key text-muted"></i></span>
                                <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="Nhập mật khẩu" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg w-100 mb-3 shadow-sm fw-bold py-3" style="border-radius: 12px;">
                            <i class="fas fa-sign-in-alt me-2"></i> Đăng nhập hệ thống
                        </button>
                    </form>
                    
                    <div class="position-relative my-4 text-center">
                        <hr class="text-muted">
                        <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">Hoặc tiếp tục với</span>
                    </div>
                    
                    <a href="<?= url('/auth/google') ?>" class="btn btn-outline-danger w-100 mb-4 py-2 fw-semibold d-flex align-items-center justify-content-center" style="border-radius: 12px;">
                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18" height="18" class="me-2">
                        Đăng nhập bằng Google
                    </a>
                    
                    <div class="text-center">
                        <p class="text-muted small mb-0">Chưa có tài khoản? <a href="<?= url('/register') ?>" class="text-success fw-bold text-decoration-none">Đăng ký ngay</a></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rules Section -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-lg bg-dark text-white" style="border-radius: 20px; overflow: hidden;">
                <div class="card-body p-5">
                    <h4 class="fw-bold mb-4 d-flex align-items-center text-warning">
                        <i class="fas fa-shield-alt me-2"></i> Quy tắc chung
                    </h4>
                    
                    <div class="rule-item d-flex gap-3 mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">1</span>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Bảo mật tuyệt đối</h6>
                            <p class="small text-white-50 mb-0">Không bao giờ chia sẻ mật khẩu hoặc mã OTP cho bất kỳ ai, kể cả nhân viên hỗ trợ.</p>
                        </div>
                    </div>

                    <div class="rule-item d-flex gap-3 mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">2</span>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Xác minh giao dịch</h6>
                            <p class="small text-white-50 mb-0">Luôn thực hiện giao dịch thông qua hệ thống để được bảo vệ quyền lợi tối đa.</p>
                        </div>
                    </div>

                    <div class="rule-item d-flex gap-3 mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">3</span>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Tuân thủ điều khoản</h6>
                            <p class="small text-white-50 mb-0">Sử dụng tài khoản đúng mục đích, không vi phạm các chính sách cộng đồng.</p>
                        </div>
                    </div>

                    <div class="rule-item d-flex gap-3 mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">4</span>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Hỗ trợ nhanh chóng</h6>
                            <p class="small text-white-50 mb-0">Nếu gặp bất kỳ vấn đề gì, hãy liên hệ ngay với đội ngũ Support qua Telegram hoặc ChatBox.</p>
                        </div>
                    </div>

                    <div class="mt-5 p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fab fa-telegram fs-2 text-info"></i>
                            <div>
                                <div class="small fw-bold">Kênh hỗ trợ chính thức</div>
                                <a href="https://t.me/specademy" target="_blank" class="text-info text-decoration-none small">@specademy</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
