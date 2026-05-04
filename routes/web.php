<?php
// routes/web.php

// Public routes
$router->get('/', [HomeController::class, 'index']);
$router->get('/products', [ProductController::class, 'index']);
$router->get('/category/{slug}', [ProductController::class, 'category']);
$router->get('/product/{slug}', [ProductController::class, 'show']);
$router->get('/search', [ProductController::class, 'search']);
$router->get('/bai-viet', [ArticleController::class, 'index']);
$router->get('/bai-viet/{slug}', [ArticleController::class, 'show']);
$router->get('/faqs', [HomeController::class, 'faqs']);
$router->get('/support', [HomeController::class, 'support']);
$router->get('/2fa', [HomeController::class, 'twoFactor']);
$router->get('/nha-ban-hang', [HomeController::class, 'sellerRegistration']);

// Auth routes
$router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class]);
$router->get('/register', [AuthController::class, 'showRegister'], [GuestMiddleware::class]);
$router->post('/register', [AuthController::class, 'register'], [GuestMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/auth/google', [AuthController::class, 'googleRedirect']);
$router->get('/auth/google/callback', [AuthController::class, 'googleCallback']);
$router->post('/register-seller', [AuthController::class, 'registerAsSeller']);

// User routes
$router->get('/user/dashboard', [UserController::class, 'dashboard'], [AuthMiddleware::class]);
$router->get('/user/profile', [UserController::class, 'profile'], [AuthMiddleware::class]);
$router->post('/user/profile', [UserController::class, 'updateProfile'], [AuthMiddleware::class]);
$router->get('/user/orders', [UserController::class, 'orders'], [AuthMiddleware::class]);
$router->get('/user/orders/{id}', [UserController::class, 'orderDetail'], [AuthMiddleware::class]);
$router->get('/user/disputes', [UserController::class, 'disputes'], [AuthMiddleware::class]);
$router->post('/user/orders/{id}/dispute', [UserController::class, 'submitDispute'], [AuthMiddleware::class]);
$router->post('/user/orders/{id}/review', [UserController::class, 'submitReview'], [AuthMiddleware::class]);
$router->get('/user/wallet', [UserController::class, 'wallet'], [AuthMiddleware::class]);
$router->post('/user/wallet/confirm-deposit', [UserController::class, 'confirmDeposit'], [AuthMiddleware::class]);
$router->get('/messages', [ChatController::class, 'userIndex'], [AuthMiddleware::class]);
$router->get('/user/favorites', [FavoriteController::class, 'index'], [AuthMiddleware::class]);
$router->post('/api/favorites/toggle', [FavoriteController::class, 'toggle'], [AuthMiddleware::class]);

// Instant Checkout (Cart disabled)
$router->post('/checkout/instant', [CheckoutController::class, 'instant'], [AuthMiddleware::class]);

// Seller routes
$router->get('/seller/dashboard', [SellerController::class, 'dashboard'], [SellerMiddleware::class]);
$router->get('/seller/products', [SellerController::class, 'products'], [SellerMiddleware::class]);
$router->get('/seller/products/create', [SellerController::class, 'createProduct'], [SellerMiddleware::class, SellerBalanceMiddleware::class]);
$router->post('/seller/products/store', [SellerController::class, 'storeProduct'], [SellerMiddleware::class, SellerBalanceMiddleware::class]);
$router->get('/seller/products/edit/{id}', [SellerController::class, 'editProduct'], [SellerMiddleware::class]);
$router->post('/seller/products/update/{id}', [SellerController::class, 'updateProduct'], [SellerMiddleware::class]);
$router->post('/seller/products/delete/{id}', [SellerController::class, 'deleteProduct'], [SellerMiddleware::class]);
$router->get('/seller/products/stock/{id}', [SellerController::class, 'manageStock'], [SellerMiddleware::class]);
$router->post('/seller/products/stock/import', [SellerController::class, 'importStock'], [SellerMiddleware::class, SellerBalanceMiddleware::class]);
$router->get('/seller/orders', [SellerController::class, 'orders'], [SellerMiddleware::class]);
$router->get('/seller/orders/{id}', [SellerController::class, 'orderDetail'], [SellerMiddleware::class]);
$router->get('/seller/disputes', [SellerController::class, 'disputes'], [SellerMiddleware::class]);
$router->post('/seller/disputes/respond/{id}', [SellerController::class, 'respondDispute'], [SellerMiddleware::class]);
$router->post('/seller/orders/{id}/status', [SellerController::class, 'updateOrderStatus'], [SellerMiddleware::class]);
$router->post('/seller/orders/{id}/refund', [SellerController::class, 'refundOrder'], [SellerMiddleware::class]);
$router->get('/seller/wallet', [SellerController::class, 'wallet'], [SellerMiddleware::class]);
$router->post('/seller/wallet/deposit', [SellerController::class, 'requestDeposit'], [SellerMiddleware::class]);
$router->post('/seller/deactivation/request', [SellerController::class, 'requestDeactivation'], [SellerMiddleware::class]);
$router->post('/seller/deactivation/cancel', [SellerController::class, 'cancelDeactivation'], [SellerMiddleware::class]);
$router->get('/seller/withdrawals', [SellerController::class, 'withdrawals'], [SellerMiddleware::class]);
$router->post('/seller/withdrawals/store', [SellerController::class, 'requestWithdrawal'], [SellerMiddleware::class]);
$router->post('/seller/telegram/update', [SellerController::class, 'updateTelegram'], [SellerMiddleware::class]);

// Chat routes
$router->get('/seller/chat', [ChatController::class, 'sellerIndex'], [SellerMiddleware::class]);
$router->get('/api/chat/messages', [ChatController::class, 'getMessages'], [AuthMiddleware::class]);
$router->get('/api/chat/conversation', [ChatController::class, 'getConversationMessages'], [AuthMiddleware::class]);
$router->get('/api/chat/list', [ChatController::class, 'getChatList'], [AuthMiddleware::class]);
$router->post('/api/chat/send', [ChatController::class, 'sendMessage'], [AuthMiddleware::class]);
$router->get('/api/notifications/check', [ChatController::class, 'checkNotifications'], [AuthMiddleware::class]);

// Affiliate routes
$router->get('/affiliate/dashboard', [AffiliateController::class, 'dashboard'], [AffiliateMiddleware::class]);
$router->get('/affiliate/commissions', [AffiliateController::class, 'commissions'], [AffiliateMiddleware::class]);

// Admin routes
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'], [AdminMiddleware::class]);
$router->get('/admin/spam-users', [AdminController::class, 'spamUsers'], [AdminMiddleware::class]);
$router->get('/admin/users', [AdminController::class, 'users'], [AdminMiddleware::class]);
$router->post('/admin/users/update-role/{id}', [AdminController::class, 'updateUserRole'], [AdminMiddleware::class]);
$router->post('/admin/users/reset-password', [AdminController::class, 'resetUserPassword'], [AdminMiddleware::class]);
$router->post('/admin/users/approve-seller', [AdminController::class, 'approveSeller'], [AdminMiddleware::class]);
$router->post('/admin/users/reject-seller', [AdminController::class, 'rejectSeller'], [AdminMiddleware::class]);
$router->post('/admin/users/toggle-status/{id}', [AdminController::class, 'toggleUserStatus'], [AdminMiddleware::class]);
$router->get('/admin/sellers', [AdminController::class, 'sellers'], [AdminMiddleware::class]);
$router->get('/admin/products', [AdminController::class, 'products'], [AdminMiddleware::class]);
$router->post('/admin/products/approve/{id}', [AdminController::class, 'approveProduct'], [AdminMiddleware::class]);
$router->post('/admin/products/reject/{id}', [AdminController::class, 'rejectProduct'], [AdminMiddleware::class]);
$router->get('/admin/orders', [AdminController::class, 'orders'], [AdminMiddleware::class]);
$router->get('/admin/withdrawals', [AdminController::class, 'withdrawals'], [AdminMiddleware::class]);
$router->post('/admin/withdrawals/approve/{id}', [AdminController::class, 'approveWithdrawal'], [AdminMiddleware::class]);
$router->post('/admin/withdrawals/reject/{id}', [AdminController::class, 'rejectWithdrawal'], [AdminMiddleware::class]);
$router->get('/admin/deposits', [AdminController::class, 'deposits'], [AdminMiddleware::class]);
$router->post('/admin/deposits/approve/{id}', [AdminController::class, 'approveDeposit'], [AdminMiddleware::class]);
$router->post('/admin/deposits/reject/{id}', [AdminController::class, 'rejectDeposit'], [AdminMiddleware::class]);
$router->get('/admin/npc-messages', [AdminController::class, 'npcMessages'], [AdminMiddleware::class]);
$router->get('/admin/disputes', [AdminController::class, 'disputes'], [AdminMiddleware::class]);
$router->post('/admin/disputes/resolve/{id}', [AdminController::class, 'resolveDispute'], [AdminMiddleware::class]);
$router->get('/admin/categories', [AdminController::class, 'categories'], [AdminMiddleware::class]);
$router->post('/admin/categories/store', [AdminController::class, 'storeCategory'], [AdminMiddleware::class]);
$router->post('/admin/categories/update/{id}', [AdminController::class, 'updateCategory'], [AdminMiddleware::class]);
$router->post('/admin/categories/delete/{id}', [AdminController::class, 'deleteCategory'], [AdminMiddleware::class]);

$router->get('/admin/menus', [AdminController::class, 'menus'], [AdminMiddleware::class]);
$router->post('/admin/menus/store', [AdminController::class, 'storeMenu'], [AdminMiddleware::class]);
$router->post('/admin/menus/update/{id}', [AdminController::class, 'updateMenu'], [AdminMiddleware::class]);
$router->post('/admin/menus/delete/{id}', [AdminController::class, 'deleteMenu'], [AdminMiddleware::class]);

$router->post('/admin/sellers/update-limit/{id}', [AdminController::class, 'updateSellerLimit'], [AdminMiddleware::class]);
$router->post('/admin/sellers/toggle-status/{id}', [AdminController::class, 'toggleSellerStatus'], [AdminMiddleware::class]);
$router->post('/admin/sellers/refund-and-ban/{id}', [AdminController::class, 'refundSellerOrdersAndBan'], [AdminMiddleware::class]);
$router->post('/admin/users/toggle-status/{id}', [AdminController::class, 'toggleUserStatus'], [AdminMiddleware::class]);

$router->get('/admin/orders', [AdminController::class, 'orders'], [AdminMiddleware::class]);
$router->get('/admin/orders/{id}', [AdminController::class, 'orderDetail'], [AdminMiddleware::class]);

    $router->get('/admin/settings', [AdminController::class, 'settings'], [AdminMiddleware::class]);
    $router->post('/admin/settings/update', [AdminController::class, 'updateSettings'], [AdminMiddleware::class]);
    $router->get('/admin/transactions', [AdminController::class, 'transactions'], [AdminMiddleware::class]);
    $router->get('/admin/error-logs', [AdminController::class, 'errorLogs'], [AdminMiddleware::class]);
    $router->get('/admin/bai-viet', [ArticleController::class, 'adminIndex'], [AdminMiddleware::class]);
$router->post('/admin/bai-viet/store', [ArticleController::class, 'store'], [AdminMiddleware::class]);
$router->post('/admin/bai-viet/update/{id}', [ArticleController::class, 'update'], [AdminMiddleware::class]);
$router->post('/admin/bai-viet/upload-image', [ArticleController::class, 'uploadImage'], [AdminMiddleware::class]);
$router->post('/admin/bai-viet/delete/{id}', [ArticleController::class, 'delete'], [AdminMiddleware::class]);

// Public seller shop route (placed at the end to avoid conflicting with /seller/dashboard etc)
$router->get('/seller/{username}', [ProductController::class, 'sellerShop']);
