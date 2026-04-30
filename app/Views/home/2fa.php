<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header py-5 text-center text-white mb-5" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
    <div class="container py-4">
        <h1 class="display-4 fw-bold mb-3 animate-fade-in-up">Công cụ 2FA</h1>
        <p class="lead animate-fade-in-up animation-delay-1">Lấy mã xác thực 2 bước (TOTP) nhanh chóng và an toàn</p>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="icon-box mx-auto mb-3 bg-light-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="fas fa-shield-alt fa-2x text-primary"></i>
                    </div>
                    <h3 class="fw-bold">Lấy mã 2FA</h3>
                    <p class="text-secondary small">Mã của bạn được tạo trực tiếp trên trình duyệt, tuyệt đối an toàn và không lưu trữ trên máy chủ.</p>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label fw-bold">Nhập mã Secret (2FA Key)</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-key"></i></span>
                        <input type="text" id="secretKey" class="form-control border-start-0 ps-0" placeholder="VD: JBSWY3DPEHPK3PXP" autocomplete="off">
                    </div>
                </div>

                <div class="d-grid gap-2 mb-4">
                    <button id="generateBtn" class="btn btn-primary btn-lg rounded-pill fw-bold" type="button">
                        <i class="fas fa-sync-alt me-2"></i> Lấy mã ngay
                    </button>
                </div>

                <!-- Result Box -->
                <div id="resultBox" class="text-center p-4 rounded-4 bg-light border d-none position-relative overflow-hidden">
                    <p class="text-muted mb-2 fw-bold">Mã xác thực của bạn:</p>
                    <h1 id="totpCode" class="display-3 fw-bold text-primary mb-0 letter-spacing-2" style="letter-spacing: 5px;">------</h1>
                    
                    <div class="mt-3">
                        <button id="copyBtn" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                            <i class="fas fa-copy me-1"></i> Copy
                        </button>
                    </div>
                    
                    <!-- Progress bar for 30s validity -->
                    <div class="progress mt-4" style="height: 4px;">
                        <div id="validityProgress" class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p id="timeRemaining" class="small text-muted mt-2 mb-0">Hiệu lực: 30s</p>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .bg-light-primary { background-color: rgba(139, 92, 246, 0.1); }
    .animate-fade-in-up {
        animation: fadeInUp 0.8s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    .animation-delay-1 { animation-delay: 0.2s; }
    @keyframes fadeInUp {
        to { opacity: 1; transform: translateY(0); }
    }
    .form-control:focus {
        border-color: #dee2e6;
        box-shadow: none;
    }
    .input-group:focus-within {
        box-shadow: 0 0 0 0.25rem rgba(139, 92, 246, 0.25);
        border-radius: 0.5rem;
    }
</style>

<!-- OTPAuth Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/otpauth/9.2.2/otpauth.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const secretInput = document.getElementById('secretKey');
    const generateBtn = document.getElementById('generateBtn');
    const resultBox = document.getElementById('resultBox');
    const totpCodeDisplay = document.getElementById('totpCode');
    const copyBtn = document.getElementById('copyBtn');
    const validityProgress = document.getElementById('validityProgress');
    const timeRemainingDisplay = document.getElementById('timeRemaining');
    
    let timerInterval = null;
    let cooldownTimer = null;
    let isCooldown = false;

    function generateTOTP() {
        if (isCooldown) return;
        
        let secret = secretInput.value.replace(/\s+/g, '').toUpperCase();
        
        if (!secret) {
            Swal.fire({
                icon: 'warning',
                title: 'Lỗi',
                text: 'Vui lòng nhập mã Secret 2FA',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            return;
        }

        try {
            // Loại bỏ các ký tự không hợp lệ của base32 nếu có
            secret = secret.replace(/[^A-Z2-7]/g, '');
            
            let totp = new OTPAuth.TOTP({
                algorithm: "SHA1",
                digits: 6,
                period: 30,
                secret: secret
            });

            let token = totp.generate();
            totpCodeDisplay.textContent = token;
            resultBox.classList.remove('d-none');
            
            // Start cooldown (5s to prevent spam)
            startCooldown(5);
            
            // Start validity timer (update every second)
            startValidityTimer();
            
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Mã không hợp lệ',
                text: 'Vui lòng kiểm tra lại mã Secret của bạn.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
    }

    function startCooldown(seconds) {
        isCooldown = true;
        generateBtn.disabled = true;
        
        let count = seconds;
        generateBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i> Vui lòng đợi ${count}s`;
        
        clearInterval(cooldownTimer);
        cooldownTimer = setInterval(() => {
            count--;
            if (count <= 0) {
                clearInterval(cooldownTimer);
                isCooldown = false;
                generateBtn.disabled = false;
                generateBtn.innerHTML = `<i class="fas fa-sync-alt me-2"></i> Lấy mã ngay`;
            } else {
                generateBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i> Vui lòng đợi ${count}s`;
            }
        }, 1000);
    }

    function startValidityTimer() {
        clearInterval(timerInterval);
        
        function updateTimer() {
            let epoch = Math.round(new Date().getTime() / 1000.0);
            let countDown = 30 - (epoch % 30);
            
            if (countDown === 30) {
                // Mã mới đã được sinh ra, tự động cập nhật nếu không trong cooldown
                // Hoặc chỉ cần cập nhật giao diện
                let secret = secretInput.value.replace(/\s+/g, '').toUpperCase().replace(/[^A-Z2-7]/g, '');
                if (secret) {
                    try {
                        let totp = new OTPAuth.TOTP({ secret: secret });
                        totpCodeDisplay.textContent = totp.generate();
                    } catch (e) {}
                }
            }
            
            let progressPercent = (countDown / 30) * 100;
            validityProgress.style.width = progressPercent + '%';
            timeRemainingDisplay.textContent = `Hiệu lực: ${countDown}s`;
            
            if (countDown < 10) {
                validityProgress.classList.remove('bg-success');
                validityProgress.classList.add('bg-danger');
                totpCodeDisplay.classList.remove('text-primary');
                totpCodeDisplay.classList.add('text-danger');
            } else {
                validityProgress.classList.remove('bg-danger');
                validityProgress.classList.add('bg-success');
                totpCodeDisplay.classList.remove('text-danger');
                totpCodeDisplay.classList.add('text-primary');
            }
        }
        
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    }

    generateBtn.addEventListener('click', generateTOTP);
    
    // Allow pressing Enter to generate
    secretInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            generateTOTP();
        }
    });

    copyBtn.addEventListener('click', function() {
        const code = totpCodeDisplay.textContent;
        if (code && code !== '------') {
            navigator.clipboard.writeText(code).then(() => {
                copyBtn.innerHTML = `<i class="fas fa-check me-1"></i> Đã copy`;
                copyBtn.classList.remove('btn-outline-primary');
                copyBtn.classList.add('btn-success', 'text-white');
                
                setTimeout(() => {
                    copyBtn.innerHTML = `<i class="fas fa-copy me-1"></i> Copy`;
                    copyBtn.classList.add('btn-outline-primary');
                    copyBtn.classList.remove('btn-success', 'text-white');
                }, 2000);
            });
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
