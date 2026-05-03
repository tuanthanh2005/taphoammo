<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Seller Dashboard' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #1a1c1e !important;
            color: #fff;
            padding: 0;
            z-index: 1060; /* Đảm bảo nằm trên backdrop của Bootstrap */
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
            background: #28a745 !important;
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
            <div class="d-md-none bg-success text-white p-3 d-flex justify-content-between align-items-center sticky-top shadow-sm">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link text-white p-0 me-3 fs-4" type="button" data-bs-toggle="offcanvas" data-bs-target="#sellerSidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="mb-0 fw-bold">Seller Panel</h5>
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
            <nav id="sellerSidebar" class="col-md-2 d-md-block sidebar offcanvas-md offcanvas-start shadow-sm border-end" tabindex="-1">
                <div class="offcanvas-header d-md-none bg-dark text-white p-4">
                    <h5 class="offcanvas-title fw-bold"><i class="fas fa-store me-2 text-success"></i> SELLER PANEL</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sellerSidebar"></button>
                </div>
                <div class="position-sticky pt-3 offcanvas-body d-block p-0">
                    <div class="px-3 mb-4 d-none d-md-block border-bottom pb-3">
                        <h5 class="fw-bold text-success mb-0"><i class="fas fa-store me-2"></i> Seller Panel</h5>
                    </div>
<?php
$db = Database::getInstance();
$sellerId = Auth::id();
// Đếm đơn hàng mới chưa đọc
$pendingOrders = $db->fetchOne("SELECT COUNT(*) as count FROM order_items WHERE seller_id = ? AND is_read = 0", [$sellerId])['count'] ?? 0;
// Đếm khiếu nại đang mở
$openDisputes = $db->fetchOne("SELECT COUNT(*) as count FROM disputes WHERE seller_id = ? AND status IN ('open', 'under_review')", [$sellerId])['count'] ?? 0;
$conversationModel = new Conversation();
$unreadMessages = $conversationModel->getTotalUnread($sellerId);
?>
                    <ul class="nav flex-column gap-2 px-2">
                        <li class="nav-item">
                            <a class="nav-link rounded-pill <?= strpos($_SERVER['REQUEST_URI'], '/seller/dashboard') !== false ? 'active shadow-sm' : '' ?>" href="<?= url('/seller/dashboard') ?>">
                                <i class="fas fa-th-large me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill <?= strpos($_SERVER['REQUEST_URI'], '/seller/products') !== false ? 'active shadow-sm' : '' ?>" href="<?= url('/seller/products') ?>">
                                <i class="fas fa-box-open me-2"></i> Sản phẩm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill" href="<?= url('/seller/products/create') ?>">
                                <i class="fas fa-plus-circle me-2"></i> Thêm sản phẩm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill d-flex justify-content-between align-items-center <?= strpos($_SERVER['REQUEST_URI'], '/seller/orders') !== false ? 'active shadow-sm' : '' ?>" href="<?= url('/seller/orders') ?>">
                                <span><i class="fas fa-shopping-basket me-2"></i> Đơn hàng</span>
                                <?php if ($pendingOrders > 0): ?>
                                    <span class="badge bg-white text-success rounded-pill"><?= $pendingOrders ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill d-flex justify-content-between align-items-center <?= strpos($_SERVER['REQUEST_URI'], '/seller/disputes') !== false ? 'active shadow-sm' : '' ?>" href="<?= url('/seller/disputes') ?>">
                                <span><i class="fas fa-exclamation-triangle me-2"></i> Khiếu nại</span>
                                <?php if ($openDisputes > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $openDisputes ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill d-flex justify-content-between align-items-center <?= strpos($_SERVER['REQUEST_URI'], '/seller/chat') !== false ? 'active shadow-sm' : '' ?>" href="<?= url('/seller/chat') ?>">
                                <span><i class="fas fa-comment-dots me-2"></i> Tin nhắn</span>
                                <?php if ($unreadMessages > 0): ?>
                                    <span class="badge bg-warning text-dark rounded-pill"><?= $unreadMessages ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item border-top border-white border-opacity-10 mt-2 pt-2">
                            <a class="nav-link rounded-pill <?= strpos($_SERVER['REQUEST_URI'], '/seller/wallet') !== false ? 'active shadow-sm' : '' ?>" href="<?= url('/seller/wallet') ?>">
                                <i class="fas fa-wallet me-2"></i> Ví tiền
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill <?= strpos($_SERVER['REQUEST_URI'], '/seller/withdrawals') !== false ? 'active shadow-sm' : '' ?>" href="<?= url('/seller/withdrawals') ?>">
                                <i class="fas fa-hand-holding-usd me-2"></i> Rút tiền
                            </a>
                        </li>
                        <li class="nav-item border-top border-white border-opacity-10 mt-2 pt-2">
                            <a class="nav-link rounded-pill" href="<?= url('/') ?>">
                                <i class="fas fa-external-link-alt me-2"></i> Về trang chủ
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= $pageTitle ?? 'Dashboard' ?></h1>
                    <div class="d-none d-md-block">
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
