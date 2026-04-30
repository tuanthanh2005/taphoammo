<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header py-5 text-center text-white mb-5" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
    <div class="container py-4">
        <h1 class="display-4 fw-bold mb-3 animate-fade-in-up">Hỗ trợ khách hàng</h1>
        <p class="lead animate-fade-in-up animation-delay-1">Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn 24/7</p>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="row g-4 justify-content-center">
        <!-- Contact Card 1 -->
        <div class="col-md-5">
            <div class="card h-100 border-0 shadow-sm text-center p-4 contact-card">
                <div class="icon-box mx-auto mb-4 bg-light-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="fab fa-telegram-plane fa-3x text-primary"></i>
                </div>
                <h4 class="fw-bold mb-3">Telegram Support</h4>
                <p class="text-secondary mb-4">Hỗ trợ nhanh nhất qua Telegram. Phản hồi trong vài phút.</p>
                <a href="https://t.me/specademy" target="_blank" class="btn btn-primary rounded-pill px-4 fw-bold mt-auto">
                    <i class="fab fa-telegram-plane me-2"></i> Nhắn tin ngay
                </a>
            </div>
        </div>

        <!-- Contact Card 2 -->
        <div class="col-md-5">
            <div class="card h-100 border-0 shadow-sm text-center p-4 contact-card">
                <div class="icon-box mx-auto mb-4 bg-light-success rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="fas fa-envelope fa-3x text-success"></i>
                </div>
                <h4 class="fw-bold mb-3">Email Contact</h4>
                <p class="text-secondary mb-4">Gửi email cho chúng tôi nếu bạn có các yêu cầu hợp tác hoặc khiếu nại phức tạp.</p>
                <a href="mailto:tetuongmmovn@gmail.com" class="btn btn-success rounded-pill px-4 fw-bold mt-auto">
                    <i class="fas fa-paper-plane me-2"></i> Gửi Email
                </a>
            </div>
        </div>
    </div>

    <!-- Working Hours -->
    <div class="row mt-5 pt-4">
        <div class="col-12 text-center">
            <div class="p-4 bg-light rounded-3 d-inline-block shadow-sm">
                <h5 class="fw-bold mb-2"><i class="far fa-clock text-primary me-2"></i> Thời gian làm việc</h5>
                <p class="mb-0 text-secondary">Thứ 2 - Chủ Nhật: 08:00 - 22:00 (Hỗ trợ kỹ thuật 24/7)</p>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-light-primary { background-color: rgba(79, 70, 229, 0.1); }
    .bg-light-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-light-warning { background-color: rgba(255, 193, 7, 0.1); }
    
    .contact-card {
        transition: all 0.3s ease;
    }
    .contact-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
    .icon-box i {
        transition: transform 0.3s ease;
    }
    .contact-card:hover .icon-box i {
        transform: scale(1.1);
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
