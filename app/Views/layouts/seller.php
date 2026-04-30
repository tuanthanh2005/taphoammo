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
            background: #28a745;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 10px 20px;
        }
        .sidebar .nav-link:hover {
            background: #218838;
        }
        .sidebar .nav-link.active {
            background: #1e7e34;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <h5 class="text-white px-3 mb-3"><i class="fas fa-store"></i> Seller Panel</h5>
<?php
$db = Database::getInstance();
$sellerId = Auth::id();
$pendingOrders = $db->fetchOne("SELECT COUNT(*) as count FROM order_items WHERE seller_id = ? AND item_status = 'processing'", [$sellerId])['count'] ?? 0;

$conversationModel = new Conversation();
$unreadMessages = $conversationModel->getTotalUnread($sellerId);
?>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/seller/dashboard') ?>">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/seller/products') ?>">
                                <i class="fas fa-box"></i> Sản phẩm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/seller/products/create') ?>">
                                <i class="fas fa-plus"></i> Thêm sản phẩm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center" href="<?= url('/seller/orders') ?>">
                                <span><i class="fas fa-shopping-cart"></i> Đơn hàng</span>
                                <?php if ($pendingOrders > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $pendingOrders ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center" href="<?= url('/seller/chat') ?>">
                                <span><i class="fas fa-comments"></i> Tin nhắn</span>
                                <?php if ($unreadMessages > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $unreadMessages ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/seller/wallet') ?>">
                                <i class="fas fa-wallet"></i> Ví tiền
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/seller/withdrawals') ?>">
                                <i class="fas fa-money-bill-wave"></i> Rút tiền
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
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
</body>
</html>
