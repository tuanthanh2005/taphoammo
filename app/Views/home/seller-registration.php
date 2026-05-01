<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="seller-reg-page py-5" style="background: #f1f5f9; min-height: 80vh;">
    <div class="container">
        <div class="row g-5">
            <!-- Left: Registration Form -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <div class="card-body p-4 p-md-5">
                        <?php if (Auth::check()): ?>
                            <div class="text-center py-5">
                                <div class="bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 80px; height: 80px; font-size: 32px;">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <h3 class="fw-bold mb-3">Bạn đã đăng nhập</h3>
                                <p class="text-muted mb-4">Để đăng ký làm nhà bán hàng, vui lòng liên hệ Admin hoặc sử dụng tài khoản khác.</p>
                                <a href="<?= url('/') ?>" class="btn btn-primary rounded-pill px-4">Quay về trang chủ</a>
                            </div>
                        <?php else: ?>
                            <div class="mb-4">
                                <h3 class="fw-bold text-dark mb-2">Đăng ký Nhà bán hàng</h3>
                                <p class="text-muted small">Bắt đầu kinh doanh sản phẩm số của bạn ngay hôm nay.</p>
                            </div>

                            <form action="<?= url('/register-seller') ?>" method="POST">
                                <?= csrf_field() ?>
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Họ tên</label>
                                        <input type="text" name="name" class="form-control rounded-3 bg-light border-0" required placeholder="VD: Trần Văn A">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Username</label>
                                        <input type="text" name="username" class="form-control rounded-3 bg-light border-0" required placeholder="username">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Email</label>
                                    <input type="email" name="email" class="form-control rounded-3 bg-light border-0" required placeholder="example@email.com">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Mật khẩu</label>
                                    <input type="password" name="password" class="form-control rounded-3 bg-light border-0" required minlength="6" placeholder="••••••••">
                                </div>

                                <div class="mb-4">
                                    <div class="form-check small">
                                        <input class="form-check-input" type="checkbox" id="agreeRules" required>
                                        <label class="form-check-label text-muted" for="agreeRules">
                                            Tôi đã đọc và đồng ý với <span class="text-primary fw-bold">Quy tắc dành cho nhà bán hàng</span>.
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm">
                                    GỬI YÊU CẦU ĐĂNG KÝ
                                </button>

                                <div class="mt-4 text-center">
                                    <p class="small text-muted mb-0">Hệ thống sẽ xem xét và phê duyệt trong vòng 24h.</p>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right: Rules & Benefits -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4"><i class="fas fa-gavel text-primary me-2"></i> Quy tắc dành cho Nhà bán hàng</h4>
                        
                        <div class="rules-list">
                            <div class="rule-item d-flex gap-3 mb-4">
                                <div class="flex-shrink-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: bold;">1</div>
                                <div>
                                    <h6 class="fw-bold mb-1">Chất lượng sản phẩm</h6>
                                    <p class="small text-muted mb-0">Mọi sản phẩm đăng bán phải hoạt động đúng như mô tả. Nghiêm cấm bán hàng giả, hàng lừa đảo.</p>
                                </div>
                            </div>

                            <div class="rule-item d-flex gap-3 mb-4">
                                <div class="flex-shrink-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: bold;">2</div>
                                <div>
                                    <h6 class="fw-bold mb-1">Quy định Bảo hành</h6>
                                    <p class="small text-muted mb-0">Seller phải tuân thủ đúng cam kết bảo hành đã đăng ký. Nếu có khiếu nại, seller có trách nhiệm xử lý trong 24h.</p>
                                </div>
                            </div>

                            <div class="rule-item d-flex gap-3 mb-4">
                                <div class="flex-shrink-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: bold;">3</div>
                                <div>
                                    <h6 class="fw-bold mb-1">Quỹ bảo chứng (Guarantee Fund)</h6>
                                    <p class="small text-muted mb-0">Để đảm bảo uy tín, Seller cần duy trì số dư tối thiểu 500,000đ trong ví để làm quỹ bảo chứng khi rút tiền.</p>
                                </div>
                            </div>

                            <div class="rule-item d-flex gap-3 mb-4">
                                <div class="flex-shrink-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: bold;">4</div>
                                <div>
                                    <h6 class="fw-bold mb-1">Phí sàn (Platform Fee)</h6>
                                    <p class="small text-muted mb-0">Hệ thống sẽ thu một khoản phí nhỏ trên mỗi đơn hàng thành công (thường từ 5-10% tùy loại sản phẩm).</p>
                                </div>
                            </div>

                            <div class="rule-item d-flex gap-3">
                                <div class="flex-shrink-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: bold;">5</div>
                                <div>
                                    <h6 class="fw-bold mb-1">Chính sách xử phạt</h6>
                                    <p class="small text-muted mb-0">Vi phạm quy định nhiều lần sẽ bị khóa tài khoản vĩnh viễn và đóng băng số dư ví.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 p-4 bg-light rounded-4">
                            <h6 class="fw-bold mb-2"><i class="fas fa-rocket text-success me-2"></i> Quyền lợi khi là Seller</h6>
                            <ul class="small text-muted mb-0 ps-3">
                                <li class="mb-1">Tiếp cận hàng ngàn khách hàng tiềm năng.</li>
                                <li class="mb-1">Hệ thống quản lý đơn hàng & kho tự động.</li>
                                <li class="mb-1">Rút tiền nhanh chóng về ngân hàng.</li>
                                <li>Hỗ trợ kỹ thuật 24/7 từ Admin.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
<div class="modal fade" id="sellerSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <div class="bg-success-subtle text-success d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; font-size: 40px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-3">Đăng ký thành công!</h4>
                <p class="text-muted mb-4">Yêu cầu làm Nhà bán hàng của bạn đã được gửi đi. Để được xét duyệt nhanh nhất, vui lòng liên hệ trực tiếp với Admin:</p>
                
                <div class="d-grid gap-2 mb-4">
                    <a href="https://t.me/specademy" target="_blank" class="btn btn-primary py-3 rounded-pill fw-bold">
                        <i class="fab fa-telegram me-2"></i> Nhắn tin qua Telegram
                    </a>
                    <a href="mailto:tetuongmmovn@gmail.com" class="btn btn-outline-dark py-3 rounded-pill fw-bold">
                        <i class="fas fa-envelope me-2"></i> Gửi Email cho Admin
                    </a>
                </div>
                
                <p class="small text-muted mb-0">Hoặc bạn có thể <a href="<?= url('/login') ?>" class="text-primary fw-bold text-decoration-none">Đăng nhập</a> và chờ đợi thông báo.</p>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('sellerSuccessModal'));
        myModal.show();
    });
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
