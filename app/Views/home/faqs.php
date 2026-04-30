<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header py-5 text-center text-white mb-5" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
    <div class="container py-4">
        <h1 class="display-4 fw-bold mb-3 animate-fade-in-up">Câu hỏi thường gặp</h1>
        <p class="lead animate-fade-in-up animation-delay-1">Giải đáp các thắc mắc của bạn khi sử dụng nền tảng AI CỦA TÔI</p>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="accordion accordion-flush shadow-sm rounded overflow-hidden" id="faqAccordion">
                
                <!-- FAQ Item 1 -->
                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            <i class="fas fa-question-circle text-primary me-3"></i> Làm thế nào để mua hàng?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body py-4 text-secondary">
                            Bạn chỉ cần chọn sản phẩm ưng ý, nhấn nút <strong>Mua ngay</strong>, chọn số lượng và thanh toán qua số dư ví. Sau khi thanh toán thành công, hệ thống sẽ tự động gửi thông tin sản phẩm (tài khoản, key,...) cho bạn ngay lập tức.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            <i class="fas fa-wallet text-primary me-3"></i> Làm sao để nạp tiền vào tài khoản?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body py-4 text-secondary">
                            Bạn vào mục <strong>Ví của tôi</strong>, chọn <strong>Nạp tiền</strong>. Hệ thống hỗ trợ nạp tiền qua Chuyển khoản ngân hàng tự động. Bạn chỉ cần quét mã QR hoặc chuyển đúng nội dung yêu cầu, tiền sẽ được cộng vào tài khoản sau 1-3 phút.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            <i class="fas fa-shield-alt text-primary me-3"></i> Chính sách bảo hành như thế nào?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body py-4 text-secondary">
                            Mỗi sản phẩm trên AI CỦA TÔI đều có chính sách bảo hành riêng do người bán quy định. Thông tin bảo hành được hiển thị rõ ràng ở trang chi tiết sản phẩm. Nếu có vấn đề phát sinh, bạn có thể liên hệ trực tiếp người bán qua hệ thống Chat hoặc yêu cầu Admin can thiệp.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            <i class="fas fa-user-tie text-primary me-3"></i> Tôi muốn trở thành người bán (Seller) phải làm sao?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body py-4 text-secondary">
                            Bạn cần đăng ký tài khoản, sau đó vào mục <strong>Kênh người bán</strong> và gửi yêu cầu kích hoạt. Sau khi Admin phê duyệt, bạn có thể đăng bán các sản phẩm kỹ thuật số của mình trên nền tảng.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                            <i class="fas fa-handshake text-primary me-3"></i> Hệ thống trung gian (Escrow) hoạt động như thế nào?
                        </button>
                    </h2>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body py-4 text-secondary">
                            Khi bạn mua hàng, tiền sẽ được hệ thống giữ lại. Chỉ khi bạn xác nhận đã nhận hàng và hài lòng, hoặc sau thời gian khiếu nại quy định, tiền mới được chuyển cho người bán. Điều này giúp đảm bảo quyền lợi tuyệt đối cho người mua.
                        </div>
                    </div>
                </div>

            </div>

            <div class="text-center mt-5">
                <p class="text-muted">Không tìm thấy câu trả lời bạn cần?</p>
                <a href="<?= url('/support') ?>" class="btn btn-primary px-4 py-2 rounded-pill fw-bold">
                    <i class="fas fa-headset me-2"></i> Liên hệ hỗ trợ ngay
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .accordion-button:not(.collapsed) {
        background-color: rgba(79, 70, 229, 0.05);
        color: #4f46e5;
        box-shadow: none;
    }
    .accordion-button:focus {
        border-color: rgba(79, 70, 229, 0.25);
        box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.1);
    }
    .accordion-item {
        border: none;
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.8s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    .animation-delay-1 {
        animation-delay: 0.2s;
    }
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
