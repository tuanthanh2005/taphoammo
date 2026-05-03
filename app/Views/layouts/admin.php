<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Dashboard' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if ($favicon = Helper::getSettingValue('site_favicon')): ?>
        <link rel="icon" type="image/x-icon" href="<?= asset($favicon) ?>">
    <?php endif; ?>
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }

        .sidebar .nav-link {
            color: #fff;
            padding: 10px 20px;
        }

        .sidebar .nav-link:hover {
            background: #495057;
        }

        .sidebar .nav-link.active {
            background: #007bff;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <h5 class="text-white px-3 mb-3"><i class="fas fa-user-shield"></i> Admin Panel</h5>
                    <?php
                    $db = Database::getInstance();
                    $adminPendingProducts = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE status = 'pending'")['count'] ?? 0;
                    $adminPendingOrders = $db->fetchOne("SELECT COUNT(*) as count FROM order_items WHERE item_status = 'processing'")['count'] ?? 0;
                    $adminPendingWithdrawals = $db->fetchOne("SELECT COUNT(*) as count FROM withdrawals WHERE status = 'pending'")['count'] ?? 0;
                    $adminPendingDeposits = $db->fetchOne("SELECT COUNT(*) as count FROM deposit_requests WHERE status = 'pending'")['count'] ?? 0;
                    ?>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/admin/dashboard') ?>">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/admin/users') ?>">
                                <i class="fas fa-users"></i> Người dùng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center"
                                href="<?= url('/admin/spam-users') ?>">
                                <span><i class="fas fa-user-slash text-danger"></i> Khách hàng spam</span>
                                <?php
                                $adminSpamCount = $db->fetchOne("SELECT COUNT(*) as count FROM (SELECT user_id FROM deposit_requests WHERE status = 'rejected' GROUP BY user_id HAVING COUNT(*) >= 3) as t")['count'] ?? 0;
                                if ($adminSpamCount > 0):
                                    ?>
                                    <span class="badge bg-warning text-dark rounded-pill"><?= $adminSpamCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/admin/sellers') ?>">
                                <i class="fas fa-store"></i> Sellers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center"
                                href="<?= url('/admin/products') ?>">
                                <span><i class="fas fa-box"></i> Sản phẩm</span>
                                <?php if ($adminPendingProducts > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $adminPendingProducts ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center"
                                href="<?= url('/admin/orders') ?>">
                                <span><i class="fas fa-shopping-cart"></i> Đơn hàng</span>
                                <?php if ($adminPendingOrders > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $adminPendingOrders ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center"
                                href="<?= url('/admin/disputes') ?>">
                                <span><i class="fas fa-balance-scale"></i> Khiếu nại</span>
                                <?php
                                $adminPendingDisputes = $db->fetchOne("SELECT COUNT(*) as count FROM disputes WHERE status IN ('open', 'under_review')")['count'] ?? 0;
                                if ($adminPendingDisputes > 0):
                                    ?>
                                    <span class="badge bg-danger rounded-pill"><?= $adminPendingDisputes ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center"
                                href="<?= url('/admin/withdrawals') ?>">
                                <span><i class="fas fa-money-bill-wave"></i> Rút tiền</span>
                                <?php if ($adminPendingWithdrawals > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $adminPendingWithdrawals ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center"
                                href="<?= url('/admin/deposits') ?>">
                                <span><i class="fas fa-qrcode"></i> Nạp tiền</span>
                                <?php if ($adminPendingDeposits > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $adminPendingDeposits ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/admin/transactions') ?>">
                                <i class="fas fa-exchange-alt"></i> Giao dịch
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/admin/categories') ?>">
                                <i class="fas fa-folder"></i> Danh mục
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/admin/menus') ?>">
                                <i class="fas fa-bars"></i> Quản lý Menu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/admin/settings') ?>">
                                <i class="fas fa-cog"></i> Cài đặt
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/admin/bai-viet') ?>">
                                <i class="fas fa-newspaper"></i> Bài viết
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/admin/error-logs') ?>">
                                <i class="fas fa-bug text-warning"></i> Nhật ký lỗi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/') ?>">
                                <i class="fas fa-home"></i> Về trang chủ
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-10 ms-sm-auto px-md-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= $pageTitle ?? 'Dashboard' ?></h1>
                    <div>
                        <span class="text-muted">Xin chào, <?= e(Auth::user()['name']) ?></span>
                        <a href="<?= url('/logout') ?>" class="btn btn-sm btn-outline-danger ms-2">Đăng xuất</a>
                    </div>
                </div>

                <?php if (Session::hasFlash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= e(Session::getFlash('success')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (Session::hasFlash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= e(Session::getFlash('error')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php echo $content ?? ''; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <?php require_once __DIR__ . '/chat_widget.php'; ?>
</body>

</html>