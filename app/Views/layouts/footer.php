    <style>
    .mobile-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 65px;
        background: #fff;
        display: flex;
        justify-content: space-around;
        align-items: center;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.05);
        z-index: 9997;
        padding-bottom: env(safe-area-inset-bottom);
        border-top: 1px solid #eee;
    }
    .mobile-bottom-nav .nav-item {
        text-decoration: none;
        color: #94a3b8;
        display: flex;
        flex-direction: column;
        align-items: center;
        font-size: 0.7rem;
        gap: 5px;
        flex: 1;
        transition: all 0.2s;
    }
    .mobile-bottom-nav .nav-item i {
        font-size: 1.2rem;
    }
    .mobile-bottom-nav .nav-item.active {
        color: #28a745;
    }
    @media (min-width: 992px) {
        .mobile-bottom-nav { display: none; }
    }
    @media (max-width: 991px) {
        body { padding-bottom: 70px !important; }
    }
    </style>

    <div class="mobile-bottom-nav">
        <a href="<?= url('/') ?>" class="nav-item <?= $_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Trang Chủ</span>
        </a>
        <a href="<?= url('/products') ?>" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/products') !== false ? 'active' : '' ?>">
            <i class="fas fa-box"></i>
            <span>Sản Phẩm</span>
        </a>
        <a href="<?= url('/user/orders') ?>" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/user/orders') !== false ? 'active' : '' ?>">
            <i class="fas fa-shopping-basket"></i>
            <span>Đơn Hàng</span>
        </a>
        <a href="<?= url('/user/favorites') ?>" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/user/favorites') !== false ? 'active' : '' ?>">
            <i class="fas fa-heart"></i>
            <span>Yêu Thích</span>
        </a>
        <a href="<?= url('/user/wallet') ?>" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/user/wallet') !== false ? 'active' : '' ?>">
            <i class="fas fa-wallet"></i>
            <span>Ví Tiền</span>
        </a>
    </div>

    <footer class="footer-ultra mt-5 pt-5 pb-5 pb-lg-4">
        <div class="container">
            <div class="row gy-4 mb-5">
                <div class="col-lg-4 col-md-6 pe-lg-5">
                    <h5 class="fw-bold text-white mb-3 d-flex align-items-center">
                        <i class="fas fa-microchip text-primary fs-3 me-2"></i> aicuatoi.com
                    </h5>
                    <p class="text-white small lh-lg mb-4">
                        Sàn giao dịch sản phẩm số hàng đầu dành cho dân công nghệ. Cung cấp tài khoản Premium, phần mềm bản quyền, công cụ AI và khóa học chuyên sâu với mức giá ưu đãi nhất.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-telegram-plane"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-bold mb-4 text-uppercase tracking-wide">Về Chúng Tôi</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="javascript:void(0)" onclick="openInfoModal('about')">Giới thiệu</a></li>
                        <li><a href="javascript:void(0)" onclick="openInfoModal('faqs')">Câu hỏi thường gặp</a></li>
                        <li><a href="javascript:void(0)" onclick="openInfoModal('terms')">Điều khoản dịch vụ</a></li>
                        <li><a href="javascript:void(0)" onclick="openInfoModal('privacy')">Chính sách bảo mật</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white fw-bold mb-4 text-uppercase tracking-wide">Hỗ Trợ Khách Hàng</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="javascript:void(0)" onclick="openInfoModal('support')">Trung tâm trợ giúp</a></li>
                        <li><a href="javascript:void(0)" onclick="openInfoModal('guide')">Hướng dẫn mua bán</a></li>
                        <li><a href="javascript:void(0)" onclick="openInfoModal('warranty')">Chính sách bảo hành</a></li>
                        <?php 
                        $dbFooter = Database::getInstance();
                        $sellerRegEnabledFooter = $dbFooter->fetchOne("SELECT value FROM settings WHERE key_name = 'enable_seller_registration'")['value'] ?? 1;
                        if ((int)$sellerRegEnabledFooter === 1): 
                        ?>
                        <li><a href="<?= url('/nha-ban-hang') ?>" class="text-warning fw-bold"><i class="fas fa-rocket me-1"></i> Trở thành người bán</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white fw-bold mb-4 text-uppercase tracking-wide">Liên Hệ</h6>
                    <div class="footer-contact">
                        <p><i class="fas fa-envelope text-primary me-2"></i> tetuongmmovn@gmail.com</p>
                        <p><i class="fab fa-telegram text-info me-2"></i> @specademy</p>
                        <p><i class="fas fa-headset text-success me-2"></i> Hỗ trợ: 08:00 - 23:00</p>
                    </div>
                </div>
            </div>

            <hr class="footer-divider mb-4">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="small mb-0" style="color: #94a3b8;">&copy; 2026 <strong class="text-white">aicuatoi.com</strong>. Bản quyền thuộc về Specademy.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="payment-methods">
                        <i class="fab fa-cc-visa fs-3 me-2" style="color: #94a3b8;"></i>
                        <i class="fab fa-cc-mastercard fs-3 me-2" style="color: #94a3b8;"></i>
                        <i class="fab fa-cc-paypal fs-3 me-2" style="color: #94a3b8;"></i>
                        <i class="fab fa-bitcoin fs-3" style="color: #94a3b8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
        .footer-ultra {
            background-color: #0f172a;
            color: #94a3b8;
            font-family: 'Inter', sans-serif;
            border-top: 1px solid #1e293b;
        }
        .footer-ultra .tracking-wide {
            letter-spacing: 0.05em;
            font-size: 0.85rem;
        }
        .footer-links li {
            margin-bottom: 12px;
        }
        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        .footer-links a:hover {
            color: #60a5fa;
            padding-left: 5px;
        }
        .footer-contact p {
            font-size: 0.9rem;
            margin-bottom: 12px;
            color: #cbd5e1;
        }
        .social-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background-color: #1e293b;
            color: #94a3b8;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .social-icon:hover {
            background-color: #3b82f6;
            color: #fff;
            transform: translateY(-3px);
        }
        .footer-divider {
            border-color: #1e293b;
            opacity: 1;
        }
        .payment-methods i {
            transition: color 0.3s ease;
        }
        .payment-methods i:hover {
            color: #cbd5e1 !important;
        }
    </style>

    <!-- Info Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold text-dark" id="infoModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-secondary" id="infoModalBody" style="line-height: 1.7;">
                </div>
            </div>
        </div>
    </div>

    <script>
        const footerModalData = {
            'about': { title: 'Giới thiệu', content: '<b>aicuatoi.com</b> là sàn giao dịch thương mại điện tử chuyên cung cấp các sản phẩm số, tài khoản phần mềm, công cụ AI uy tín và chất lượng hàng đầu. Chúng tôi tự hào mang đến cho dân công nghệ những giải pháp tiết kiệm và hiệu quả nhất.' },
            'faqs': { title: 'Câu hỏi thường gặp', content: '<b class="text-dark">1. Tôi nhận hàng như thế nào?</b><br>Hàng sẽ được giao tự động qua email hoặc hiển thị ngay trong chi tiết đơn hàng của bạn sau khi thanh toán thành công.<br><br><b class="text-dark">2. Có hỗ trợ bảo hành không?</b><br>Có, 100% sản phẩm trên sàn đều có chính sách bảo hành rõ ràng từ người bán (1 đổi 1 nếu lỗi).' },
            'terms': { title: 'Điều khoản dịch vụ', content: 'Người mua có trách nhiệm bảo mật tài khoản đã mua. Không sử dụng sản phẩm cho mục đích vi phạm pháp luật. aicuatoi.com đóng vai trò là trung gian đảm bảo giao dịch an toàn giữa người bán và người mua.' },
            'privacy': { title: 'Chính sách bảo mật', content: 'Chúng tôi cam kết tuyệt đối không chia sẻ thông tin cá nhân (email, số điện thoại) của bạn cho bên thứ ba. Mọi dữ liệu giao dịch và thông tin thanh toán đều được mã hóa chuẩn SSL.' },
            'support': { title: 'Trung tâm trợ giúp', content: '<div class="alert alert-info border-0"><i class="fab fa-telegram me-2"></i>Liên hệ Telegram: <b>@specademy</b><br><br><i class="fas fa-envelope me-2"></i>Email: <b>tetuongmmovn@gmail.com</b><br><br><i class="fas fa-clock me-2"></i>Thời gian làm việc: <b>08:00 - 23:00 (Hàng ngày)</b></div>' },
            'guide': { title: 'Hướng dẫn mua bán', content: '<b>Bước 1:</b> Tìm kiếm và chọn sản phẩm/gói dịch vụ phù hợp.<br><b>Bước 2:</b> Bấm Mua Ngay hoặc nạp tiền vào ví hệ thống.<br><b>Bước 3:</b> Xác nhận thanh toán qua QR Code hoặc số dư ví.<br><b>Bước 4:</b> Vào mục <i>Đơn Hàng</i> để lấy thông tin tài khoản/sản phẩm đã mua.' },
            'warranty': { title: 'Chính sách bảo hành', content: '<ul class="mb-0"><li>Bảo hành theo đúng thời gian cam kết của từng sản phẩm.</li><li>Từ chối bảo hành nếu khách hàng tự ý thay đổi thông tin (đổi pass, đổi email phục hồi) trái quy định của người bán.</li><li>Thời gian xử lý khiếu nại tối đa 24h làm việc.</li></ul>' }
        };

        function openInfoModal(key) {
            document.getElementById('infoModalTitle').innerHTML = footerModalData[key].title;
            document.getElementById('infoModalBody').innerHTML = footerModalData[key].content;
            const modal = new bootstrap.Modal(document.getElementById('infoModal'));
            modal.show();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        async function toggleFavorite(btn, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            <?php if (!Auth::check()): ?>
                Swal.fire({
                    icon: 'info',
                    title: 'Yêu cầu đăng nhập',
                    text: 'Vui lòng đăng nhập để lưu sản phẩm yêu thích.',
                    showCancelButton: true,
                    confirmButtonText: 'Đăng nhập ngay',
                    cancelButtonText: 'Để sau'
                }).then((result) => {
                    if (result.isConfirmed) window.location.href = '<?= url('/login') ?>';
                });
                return;
            <?php endif; ?>

            const productId = btn.dataset.id;
            const icon = btn.querySelector('i');
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('csrf_token', '<?= csrf_token() ?>');

            try {
                const response = await fetch('<?= url('/api/favorites/toggle') ?>', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    if (result.status === 'added') {
                        icon.classList.remove('far');
                        icon.classList.add('fas', 'text-danger');
                    } else {
                        icon.classList.remove('fas', 'text-danger');
                        icon.classList.add('far');
                    }
                    
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: result.message
                    });
                }
            } catch (err) {
                console.error(err);
            }
        }
    </script>
    <?php require_once __DIR__ . '/chat_widget.php'; ?>
    <script src="<?= asset('js/main.js') ?>"></script>
    <script>
        // SMART GLOBAL LOADER LOGIC
        (function() {
            let loaderTimeout;
            const showLoader = () => {
                const loader = document.getElementById('global-loader');
                if (loader) loader.classList.add('active');
            };

            const hideLoader = () => {
                clearTimeout(loaderTimeout);
                const loader = document.getElementById('global-loader');
                if (loader) loader.classList.remove('active');
            };

            // Only show loader for form submissions to prevent "hanging" on menu navigation
            document.addEventListener('submit', (e) => {
                if (!e.target.hasAttribute('data-no-loader')) {
                    showLoader();
                }
            });

            window.addEventListener('pageshow', (event) => {
                hideLoader();
            });

            // Ensure loader is hidden when navigating back/forward
            window.onpopstate = hideLoader;
        })();
    </script>
</body>
</html>
