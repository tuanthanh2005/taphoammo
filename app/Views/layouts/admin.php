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
            background: #1a1c1e !important;
            color: #fff;
            padding: 0;
            z-index: 1060;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.7) !important;
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.2s;
            border-radius: 0;
            margin: 0;
        }

        .sidebar .nav-link:hover {
            color: #fff !important;
            background: rgba(255,255,255,0.05) !important;
        }

        .sidebar .nav-link.active {
            color: #fff !important;
            background: #007bff !important;
        }

        @media (max-width: 767.98px) {
            .sidebar {
                width: 280px !important;
            }
            .sidebar .nav-link {
                margin: 2px 10px;
                border-radius: 10px !important;
            }
            main { padding: 15px !important; }
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            overflow: hidden;
        }
        .modal-header {
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Mobile Header -->
            <div class="d-md-none bg-dark text-white p-3 d-flex justify-content-between align-items-center sticky-top shadow-sm">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link text-white p-0 me-3 fs-4" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="mb-0 fw-bold">Admin Panel</h5>
                </div>
                <div class="dropdown">
                    <a href="#" class="text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fs-4"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><span class="dropdown-item-text small text-muted">Chào, <?= e(Auth::user()['name']) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('/logout') ?>"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a></li>
                    </ul>
                </div>
            </div>

            <!-- Sidebar -->
            <nav id="adminSidebar" class="col-md-2 d-md-block sidebar offcanvas-md offcanvas-start shadow-sm border-end" tabindex="-1">
                <div class="offcanvas-header d-md-none bg-dark text-white p-4">
                    <h5 class="offcanvas-title fw-bold"><i class="fas fa-user-shield me-2 text-primary"></i> ADMIN PANEL</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar"></button>
                </div>
                <div class="position-sticky pt-3 offcanvas-body d-block p-0">
                    <div class="px-3 mb-4 d-none d-md-block border-bottom pb-3">
                        <h5 class="fw-bold text-white mb-0"><i class="fas fa-user-shield me-2"></i> Admin Panel</h5>
                    </div>
                    <?php
                    $db = Database::getInstance();
                    $adminPendingProducts = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE status = 'pending'")['count'] ?? 0;
                    $adminPendingOrders = $db->fetchOne("SELECT COUNT(*) as count FROM order_items WHERE item_status = 'processing'")['count'] ?? 0;
                    $adminPendingWithdrawals = $db->fetchOne("SELECT COUNT(*) as count FROM withdrawals WHERE status = 'pending'")['count'] ?? 0;
                    $adminPendingDeposits = $db->fetchOne("SELECT COUNT(*) as count FROM deposit_requests WHERE status = 'pending'")['count'] ?? 0;
                    ?>
                    <ul class="nav flex-column gap-1 px-2">
                        <li class="nav-item">
                            <a class="nav-link rounded-pill" href="<?= url('/admin/dashboard') ?>">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill" href="<?= url('/admin/users') ?>">
                                <i class="fas fa-users me-2"></i> Người dùng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill d-flex justify-content-between align-items-center"
                                href="<?= url('/admin/spam-users') ?>">
                                <span><i class="fas fa-user-slash text-danger me-2"></i> Spam users</span>
                                <?php
                                $adminSpamCount = $db->fetchOne("SELECT COUNT(*) as count FROM (SELECT user_id FROM deposit_requests WHERE status = 'rejected' GROUP BY user_id HAVING COUNT(*) >= 3) as t")['count'] ?? 0;
                                if ($adminSpamCount > 0):
                                    ?>
                                    <span class="badge bg-warning text-dark rounded-pill"><?= $adminSpamCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill" href="<?= url('/admin/sellers') ?>">
                                <i class="fas fa-store me-2 text-success"></i> Sellers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill d-flex justify-content-between align-items-center"
                                href="<?= url('/admin/products') ?>">
                                <span><i class="fas fa-box me-2 text-info"></i> Sản phẩm</span>
                                <?php if ($adminPendingProducts > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $adminPendingProducts ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill d-flex justify-content-between align-items-center"
                                href="<?= url('/admin/orders') ?>">
                                <span><i class="fas fa-shopping-cart me-2 text-warning"></i> Đơn hàng</span>
                                <?php if ($adminPendingOrders > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $adminPendingOrders ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill d-flex justify-content-between align-items-center"
                                href="<?= url('/admin/disputes') ?>">
                                <span><i class="fas fa-balance-scale me-2 text-danger"></i> Khiếu nại</span>
                                <?php
                                $adminPendingDisputes = $db->fetchOne("SELECT COUNT(*) as count FROM disputes WHERE status IN ('open', 'under_review')")['count'] ?? 0;
                                if ($adminPendingDisputes > 0):
                                    ?>
                                    <span class="badge bg-danger rounded-pill"><?= $adminPendingDisputes ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill d-flex justify-content-between align-items-center"
                                href="<?= url('/admin/withdrawals') ?>">
                                <span><i class="fas fa-money-bill-wave me-2 text-success"></i> Rút tiền</span>
                                <?php if ($adminPendingWithdrawals > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $adminPendingWithdrawals ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill d-flex justify-content-between align-items-center"
                                href="<?= url('/admin/deposits') ?>">
                                <span><i class="fas fa-qrcode me-2 text-primary"></i> Nạp tiền</span>
                                <?php if ($adminPendingDeposits > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $adminPendingDeposits ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item border-top border-secondary mt-2 pt-2">
                            <a class="nav-link rounded-pill" href="<?= url('/admin/transactions') ?>">
                                <i class="fas fa-exchange-alt me-2 text-info"></i> Giao dịch
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill" href="<?= url('/admin/settings') ?>">
                                <i class="fas fa-cog me-2 text-secondary"></i> Cài đặt
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill" href="<?= url('/') ?>">
                                <i class="fas fa-home me-2"></i> Trang chủ
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