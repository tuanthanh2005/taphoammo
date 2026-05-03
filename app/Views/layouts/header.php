<?php
if (Auth::check()) {
    Auth::updateLastActive();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="<?= $meta_description ?? 'AI CỦA TÔI - Nền tảng mua bán sản phẩm số uy tín #1 Việt Nam. Chuyên cung cấp tài khoản, key phần mềm, khóa học với giá tốt nhất.' ?>">
    <meta name="keywords" content="<?= $meta_keywords ?? 'mmo, sản phẩm số, tài khoản game, key phần mềm, khóa học online, marketplace' ?>">
    <meta name="author" content="AI CỦA TÔI">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= url($_SERVER['REQUEST_URI'] ?? '/') ?>">
    <meta property="og:title" content="<?= $title ?? 'AI CỦA TÔI' ?>">
    <meta property="og:description" content="<?= $meta_description ?? 'AI CỦA TÔI - Nền tảng mua bán sản phẩm số uy tín #1 Việt Nam.' ?>">
    <meta property="og:image" content="<?= $og_image ?? asset('images/default-og.jpg') ?>">
    <title><?= $title ?? 'AI CỦA TÔI' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <?php if ($favicon = Helper::getSettingValue('site_favicon')): ?>
        <link rel="icon" type="image/x-icon" href="<?= asset($favicon) ?>">
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="<?= url('/') ?>">
                <i class="fas fa-store"></i> AI CỦA TÔI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <?php
                    $menuModel = new Menu();
                    $menuTree = $menuModel->getTree();
                    foreach ($menuTree as $menu):
                        if (empty($menu['children'])):
                    ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url($menu['url']) ?>">
                                <?php if ($menu['icon']): ?><i class="<?= e($menu['icon']) ?>"></i><?php endif; ?>
                                <?= e($menu['title']) ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="<?= url($menu['url']) ?>" id="menuDropdown<?= $menu['id'] ?>" role="button" data-bs-toggle="dropdown">
                                <?php if ($menu['icon']): ?><i class="<?= e($menu['icon']) ?>"></i><?php endif; ?>
                                <?= e($menu['title']) ?>
                            </a>
                            <ul class="dropdown-menu border-0 shadow-sm">
                                <?php foreach ($menu['children'] as $child): ?>
                                <li><a class="dropdown-item py-2" href="<?= url($child['url']) ?>">
                                    <?php
                                    $iconClass = !empty($child['icon']) ? $child['icon'] : 'fas fa-chevron-right';
                                    if (strpos($iconClass, ' ') === false) {
                                        $iconClass = 'fas ' . $iconClass;
                                        if (strpos($iconClass, 'fa-bitcoin') !== false) {
                                            $iconClass = 'fab fa-bitcoin';
                                        }
                                    }
                                    ?>
                                    <i class="<?= $iconClass ?> text-success" style="width: 20px;"></i> <?= e($child['title']) ?>
                                </a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </ul>

                <ul class="navbar-nav align-items-center d-none d-lg-flex">
                    <?php if (Auth::check()): ?>
                        <?php
                        $headerWalletService = new WalletService();
                        $headerWallet = $headerWalletService->getWallet(Auth::id());
                        $headerWalletBalance = $headerWallet['balance'] ?? 0;
                        ?>
                        <li class="nav-item me-2 position-relative">
                            <button onclick="event.stopPropagation(); toggleInboxWidget()" class="btn btn-link p-2 d-flex align-items-center justify-content-center position-relative text-secondary shadow-none border-0" style="width: 40px; height: 40px; text-decoration: none;">
                                <i class="fas fa-comment-dots fs-5"></i>
                                <?php $unreadCount = (new Conversation())->getTotalUnread(Auth::id()); ?>
                                <span id="globalUnreadBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?= $unreadCount > 0 ? '' : 'd-none' ?>" style="font-size: 0.65rem; padding: 0.25em 0.6em; border: 2px solid #fff;">
                                    <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                                </span>
                            </button>
                        </li>

                        <li class="nav-item me-2">
                            <a class="btn btn-success rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2" href="<?= url('/user/wallet') ?>" style="font-size: 0.95rem;">
                                <i class="fas fa-wallet"></i>
                                <span><?= compact_money($headerWalletBalance) ?></span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle fs-3 text-secondary"></i>
                                <span><?= e(Auth::user()['name']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (Auth::isAdmin()): ?>
                                    <li><a class="dropdown-item" href="<?= url('/admin/dashboard') ?>"><i class="fas fa-tachometer-alt"></i> Admin</a></li>
                                <?php endif; ?>
                                <?php if (Auth::isSeller()): ?>
                                    <li><a class="dropdown-item" href="<?= url('/seller/dashboard') ?>"><i class="fas fa-store"></i> Seller</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= url('/user/dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= url('/user/orders') ?>"><i class="fas fa-box text-success"></i> Đơn hàng</a></li>
                                <li><a class="dropdown-item" href="<?= url('/user/favorites') ?>"><i class="fas fa-heart text-danger"></i> Sản phẩm yêu thích</a></li>
                                <li><a class="dropdown-item" href="<?= url('/user/disputes') ?>"><i class="fas fa-balance-scale text-danger"></i> Khiếu nại</a></li>
                                <li><a class="dropdown-item" href="<?= url('/user/wallet') ?>"><i class="fas fa-wallet text-info"></i> Ví tiền</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= url('/logout') ?>"><i class="fas fa-sign-out-alt text-secondary"></i> Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/login') ?>"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-success text-white ms-2" href="<?= url('/register') ?>">Đăng ký</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav d-lg-none border-top mt-2 pt-3">
                    <?php if (Auth::check()): ?>
                        <li class="nav-item">
                            <span class="nav-link fw-bold text-dark text-uppercase small opacity-50 mb-2">Tài khoản</span>
                        </li>
                        <?php if (Auth::isAdmin()): ?>
                            <li class="nav-item"><a class="nav-link py-2" href="<?= url('/admin/dashboard') ?>"><i class="fas fa-user-shield me-2 text-primary"></i> Quản trị Admin</a></li>
                        <?php endif; ?>
                        <?php if (Auth::isSeller()): ?>
                            <li class="nav-item"><a class="nav-link py-2" href="<?= url('/seller/dashboard') ?>"><i class="fas fa-store me-2 text-success"></i> Kênh Nhà bán hàng</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link py-2" href="<?= url('/user/dashboard') ?>"><i class="fas fa-user-circle me-2"></i> Trang cá nhân</a></li>
                        <li class="nav-item"><a class="nav-link py-2" href="<?= url('/user/orders') ?>"><i class="fas fa-box me-2 text-success"></i> Đơn hàng đã mua</a></li>
                        <li class="nav-item"><a class="nav-link py-2" href="<?= url('/user/favorites') ?>"><i class="fas fa-heart me-2 text-danger"></i> Sản phẩm yêu thích</a></li>
                        <li class="nav-item"><a class="nav-link py-2" href="<?= url('/user/disputes') ?>"><i class="fas fa-balance-scale me-2 text-warning"></i> Khiếu nại</a></li>
                        <li class="nav-item mt-2"><a class="nav-link py-2 text-danger fw-bold" href="<?= url('/logout') ?>"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link py-2" href="<?= url('/login') ?>"><i class="fas fa-sign-in-alt me-2 text-success"></i> Đăng nhập</a></li>
                        <li class="nav-item"><a class="nav-link py-2" href="<?= url('/register') ?>"><i class="fas fa-user-plus me-2 text-primary"></i> Đăng ký tài khoản</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (Session::hasFlash('success')): ?>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: '<?= e(Session::getFlash('success')) ?>',
                    timer: 3000,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
            });
        </script>
    <?php endif; ?>

    <?php if (Session::hasFlash('error')): ?>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: '<?= e(Session::getFlash('error')) ?>',
                    confirmButtonColor: '#d33'
                });
            });
        </script>
    <?php endif; ?>


<?php if (Auth::check()): ?>
<script>
(function() {
    // Chỉ giữ lại logic đồng bộ badge nếu cần, 
    // hoặc có thể chuyển hết vào chat_widget.php
})();
</script>
<?php endif; ?>

