<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Seller Dashboard' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1e1f24;
            --sidebar-active: #28a745;
            --sidebar-hover: rgba(255,255,255,0.08);
        }

        body {
            background-color: #f8f9fa;
        }

        .seller-layout {
            display: flex;
            align-items: stretch;
            min-height: 100vh;
            width: 100%;
        }

        /* Sidebar */
        .sidebar {
            min-height: 100vh;
            background: var(--sidebar-bg) !important;
            width: var(--sidebar-width);
            flex: 0 0 var(--sidebar-width);
            transition: all 0.3s ease;
            z-index: 1060;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 14px 20px;
            font-weight: 500;
            border-radius: 12px;
            margin: 4px 16px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar .nav-link:hover {
            color: #fff !important;
            background: var(--sidebar-hover) !important;
            transform: translateX(4px);
        }

        .sidebar .nav-link.active {
            color: #fff !important;
            background: var(--sidebar-active) !important;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .sidebar .nav-link .badge {
            font-size: 0.75em;
            padding: 4px 8px;
            min-width: 20px;
            text-align: center;
        }

        /* Mobile Sidebar */
        @media (max-width: 991.98px) {
            .seller-layout {
                display: block;
                min-height: 100vh;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 280px !important;
                transform: translateX(-100%);
                overflow-y: auto;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            main {
                padding: 20px 15px !important;
            }
        }

        /* Main Content */
        main {
            min-height: 100vh;
            padding: 24px;
            transition: all 0.3s ease;
            flex: 1 1 auto;
            min-width: 0;
        }

        .page-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 32px;
        }

        .user-info {
            font-size: 0.95em;
            color: #6c757d;
        }

        /* Mobile Header */
        .mobile-header {
            backdrop-filter: blur(10px);
            background: rgba(40, 167, 69, 0.95);
        }

        /* Divider */
        .sidebar-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 20px 16px;
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Loading state */
        .btn-loading {
            pointer-events: none;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="seller-layout">
            <!-- Mobile Header -->
            <header class="d-lg-none mobile-header text-white p-3 sticky-top shadow-lg">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <button class="sidebar-toggle btn btn-link text-white p-0 me-3 fs-4" type="button">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-store me-2"></i>Seller Panel
                        </h5>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="text-white text-decoration-none dropdown-toggle d-flex align-items-center gap-2 p-2 rounded-pill" 
                           data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fs-4"></i>
                            <span class="fw-semibold small d-none d-sm-inline"><?= substr(e(Auth::user()['name'] ?? 'User'), 0, 15) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                            <li>
                                <span class="dropdown-item-text px-3 py-2">
                                    <strong><?= e(Auth::user()['name']) ?></strong><br>
                                    <small class="text-muted"><?= e(Auth::user()['email']) ?></small>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= url('/seller/profile') ?>">
                                <i class="fas fa-user me-2"></i> Hồ sơ
                            </a></li>
                            <li><a class="dropdown-item text-danger" href="<?= url('/logout') ?>">
                                <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                            </a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Sidebar -->
            <nav id="sellerSidebar" class="sidebar d-lg-block border-end shadow-sm">
                <div class="p-4 border-bottom border-dark border-opacity-25">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-25 p-2 rounded-circle shadow-sm">
                            <i class="fas fa-store fs-4 text-success"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-white text-truncate"><?= e(Auth::user()['name']) ?></h6>
                            <small class="text-white-50">Người bán</small>
                        </div>
                    </div>
                </div>

                <div class="position-sticky pt-4 px-2" style="top: 0;">
<?php
$db = Database::getInstance();
$sellerId = Auth::id();
$pendingOrders = $db->fetchOne("SELECT COUNT(*) as count FROM order_items WHERE seller_id = ? AND is_read = 0", [$sellerId])['count'] ?? 0;
$openDisputes = $db->fetchOne("SELECT COUNT(*) as count FROM disputes WHERE seller_id = ? AND status IN ('open', 'under_review')", [$sellerId])['count'] ?? 0;
$conversationModel = new Conversation();
$unreadMessages = $conversationModel->getTotalUnread($sellerId);
$sellerCurrentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$sellerIsActive = function ($path) use ($sellerCurrentPath) {
    return strpos($sellerCurrentPath, $path) === 0;
};
?>
                    <ul class="nav flex-column gap-1">
                        <!-- Core Navigation -->
                        <li class="nav-item">
                            <a class="nav-link <?= $sellerIsActive('/seller/dashboard') ? 'active' : '' ?>" href="<?= url('/seller/dashboard') ?>">
                                <i class="fas fa-gauge-high"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= $sellerIsActive('/seller/products') ? 'active' : '' ?>" href="<?= url('/seller/products') ?>">
                                <i class="fas fa-boxes-stacked"></i>
                                <span>Sản phẩm</span>
                                <span class="badge bg-light text-dark ms-auto small rounded-pill" id="product-count">0</span>
                            </a>
                        </li>

                        <!-- Quick Actions -->
                        <li class="sidebar-divider"></li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/seller/products/create') ?>">
                                <i class="fas fa-plus text-success"></i>
                                <span>Thêm sản phẩm</span>
                            </a>
                        </li>

                        <!-- Orders & Support -->
                        <li class="sidebar-divider"></li>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between <?= $sellerIsActive('/seller/orders') ? 'active' : '' ?>" href="<?= url('/seller/orders') ?>">
                                <span><i class="fas fa-shopping-cart"></i> Đơn hàng</span>
                                <?php if ($pendingOrders > 0): ?>
                                    <span class="badge bg-danger"><?= $pendingOrders ?></span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between <?= $sellerIsActive('/seller/disputes') ? 'active' : '' ?>" href="<?= url('/seller/disputes') ?>">
                                <span><i class="fas fa-gavel"></i> Khiếu nại</span>
                                <?php if ($openDisputes > 0): ?>
                                    <span class="badge bg-warning text-dark"><?= $openDisputes ?></span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between <?= $sellerIsActive('/seller/chat') ? 'active' : '' ?>" href="<?= url('/seller/chat') ?>">
                                <span><i class="fas fa-comments"></i> Tin nhắn</span>
                                <?php if ($unreadMessages > 0): ?>
                                    <span class="badge bg-primary"><?= $unreadMessages ?></span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <!-- Financial -->
                        <li class="sidebar-divider"></li>
                        <li class="nav-item">
                            <a class="nav-link <?= $sellerIsActive('/seller/wallet') ? 'active' : '' ?>" href="<?= url('/seller/wallet') ?>">
                                <i class="fas fa-wallet"></i>
                                <span>Ví tiền</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link <?= $sellerIsActive('/seller/withdrawals') ? 'active' : '' ?>" href="<?= url('/seller/withdrawals') ?>">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Rút tiền</span>
                            </a>
                        </li>

                        <!-- Account -->
                        <li class="sidebar-divider mt-auto"></li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/seller/profile') ?>">
                                <i class="fas fa-user-cog"></i>
                                <span>Hồ sơ</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="<?= url('/logout') ?>">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main>
                <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h1 class="h3 fw-bold mb-1"><?= $pageTitle ?? 'Dashboard' ?></h1>
                        <div class="user-info d-flex align-items-center gap-2">
                            <i class="fas fa-circle text-success small"></i>
                            <span>Xin chào, <strong><?= e(Auth::user()['name']) ?></strong></span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm d-lg-none" onclick="toggleSidebar()">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>

                <!-- Flash Messages -->
                <?php if (Session::hasFlash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= e(Session::getFlash('success')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (Session::hasFlash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= e(Session::getFlash('error')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Page Content -->
                <div class="content">
                    <?php echo $content ?? ''; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile Sidebar Toggle
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.getElementById('sellerSidebar');
        
        function toggleSidebar() {
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        }

        sidebarToggle?.addEventListener('click', toggleSidebar);

        // Close sidebar on route change (for SPA-like behavior)
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !sidebarToggle?.contains(e.target)) {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        });

    </script>
    <?php require_once __DIR__ . '/chat_widget.php'; ?>
</body>
</html>
