    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-store"></i> AI CỦA TÔI</h5>
                    <p>Nền tảng mua bán sản phẩm số uy tín, chất lượng cao.</p>
                </div>
                <div class="col-md-4">
                    <h5>Liên hệ</h5>
                    <p>
                        <i class="fas fa-envelope"></i> tetuongmmovn@gmail.com<br>
                        <i class="fab fa-telegram"></i> @specademy
                    </p>
                </div>
                <div class="col-md-4">
                    <h5>Liên kết</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= url('/faqs') ?>" class="text-white text-decoration-none">Câu hỏi thường gặp</a></li>
                        <li><a href="<?= url('/support') ?>" class="text-white text-decoration-none">Hỗ trợ khách hàng</a></li>
                        <li><a href="<?= url('/nha-ban-hang') ?>" class="text-white text-decoration-none fw-bold text-warning"><i class="fas fa-rocket me-1"></i> Trở thành Nhà bán hàng</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Điều khoản sử dụng</a></li>
                    </ul>
                </div>
            </div>
            <hr class="bg-white">
            <div class="text-center">
                <p class="mb-0">&copy; 2026 AI CỦA TÔI. All rights reserved.</p>
            </div>
        </div>
    </footer>

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
</body>
</html>
