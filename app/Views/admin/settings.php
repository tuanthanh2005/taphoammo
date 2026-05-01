<?php
ob_start();
?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-cog me-2"></i> Cài đặt hệ thống</h5>
    </div>
    <div class="card-body px-4 pb-4">
        <form action="<?= url('/admin/settings/update') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="row g-4">
                <!-- Website Info -->
                <div class="col-12">
                    <h6 class="fw-bold mb-3 text-dark border-start border-4 border-primary ps-3">Thông tin website</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tên website</label>
                            <input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? 'AI CỦA TÔI') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email liên hệ</label>
                            <input type="email" name="site_email" class="form-control" value="<?= e($settings['site_email'] ?? '') ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Số điện thoại</label>
                            <input type="text" name="site_phone" class="form-control" value="<?= e($settings['site_phone'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Fees Config -->
                <div class="col-12 mt-5">
                    <h6 class="fw-bold mb-3 text-dark border-start border-4 border-primary ps-3">Cấu hình phí & Thanh toán</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Phí admin đơn hàng (%)</label>
                            <input type="number" name="admin_order_fee_percent" class="form-control" value="<?= e($settings['admin_order_fee_percent'] ?? 5) ?>" min="0" max="100" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Phí rút tiền seller (%)</label>
                            <input type="number" name="seller_withdraw_fee_percent" class="form-control" value="<?= e($settings['seller_withdraw_fee_percent'] ?? 5) ?>" min="0" max="100" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Số tiền rút tối thiểu (VNĐ)</label>
                            <input type="number" name="min_withdraw_amount" class="form-control" value="<?= e($settings['min_withdraw_amount'] ?? 50000) ?>" min="0" step="1000">
                        </div>
                    </div>
                </div>

                <!-- Features Toggle -->
                <div class="col-12 mt-5">
                    <h6 class="fw-bold mb-3 text-dark border-start border-4 border-primary ps-3">Cấu hình chức năng</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-check form-switch card p-3 shadow-none border h-100">
                                <input type="hidden" name="enable_product_approval" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="enable_product_approval" value="1" <?= ($settings['enable_product_approval'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold">Duyệt sản phẩm seller</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch card p-3 shadow-none border h-100">
                                <input type="hidden" name="enable_google_login" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="enable_google_login" value="1" <?= ($settings['enable_google_login'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold">Đăng nhập Google</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch card p-3 shadow-none border h-100">
                                <input type="hidden" name="enable_affiliate" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="enable_affiliate" value="1" <?= ($settings['enable_affiliate'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold">Hệ thống affiliate</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card p-3 shadow-none border">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <label class="form-label mb-0 fw-semibold">Hoa hồng affiliate (%)</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="number" name="affiliate_commission_percent" class="form-control" value="<?= e($settings['affiliate_commission_percent'] ?? 5) ?>" min="0" max="100" step="0.1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deposit Config -->
                <div class="col-12 mt-5">
                    <h6 class="fw-bold mb-3 text-dark border-start border-4 border-primary ps-3">Cấu hình nạp tiền (VietQR)</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Mã ngân hàng</label>
                            <input type="text" name="deposit_bank_code" class="form-control" value="<?= e($settings['deposit_bank_code'] ?? 'mb') ?>" placeholder="mb, vietinbank...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tên ngân hàng</label>
                            <input type="text" name="deposit_bank_name" class="form-control" value="<?= e($settings['deposit_bank_name'] ?? 'MB Bank') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Chủ tài khoản</label>
                            <input type="text" name="deposit_account_name" class="form-control" value="<?= e($settings['deposit_account_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Số tài khoản</label>
                            <input type="text" name="deposit_account_number" class="form-control" value="<?= e($settings['deposit_account_number'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Telegram Config -->
                <div class="col-12 mt-5">
                    <h6 class="fw-bold mb-3 text-dark border-start border-4 border-primary ps-3">Thông báo Telegram</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Telegram Username</label>
                            <input type="text" name="telegram_support_username" class="form-control" value="<?= e($settings['telegram_support_username'] ?? '@specademy') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Telegram URL</label>
                            <input type="text" name="telegram_support_url" class="form-control" value="<?= e($settings['telegram_support_url'] ?? 'https://t.me/specademy') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Telegram Chat ID</label>
                            <input type="text" name="telegram_chat_id" class="form-control" value="<?= e($settings['telegram_chat_id'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Telegram Bot Token</label>
                            <input type="text" name="telegram_bot_token" class="form-control" value="<?= e($settings['telegram_bot_token'] ?? '') ?>" placeholder="123456:ABC...">
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <h6 class="fw-bold mb-3 text-dark border-start border-4 border-danger ps-3">Telegram riêng cho ví tiền (nạp / rút)</h6>
                    <div class="alert alert-danger-subtle border rounded-4 small mb-3">
                        Dùng riêng bot và chat ID cho các giao dịch ví tiền để dễ theo dõi và an toàn hơn. Nếu bỏ trống, hệ thống sẽ fallback về Telegram chung.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Telegram Username ví</label>
                            <input type="text" name="wallet_telegram_support_username" class="form-control" value="<?= e($settings['wallet_telegram_support_username'] ?? '') ?>" placeholder="@wallet_admin">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Telegram URL ví</label>
                            <input type="text" name="wallet_telegram_support_url" class="form-control" value="<?= e($settings['wallet_telegram_support_url'] ?? '') ?>" placeholder="https://t.me/wallet_admin">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Telegram Chat ID ví</label>
                            <input type="text" name="wallet_telegram_chat_id" class="form-control" value="<?= e($settings['wallet_telegram_chat_id'] ?? '') ?>" placeholder="8199725778">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Telegram Bot Token ví</label>
                            <input type="text" name="wallet_telegram_bot_token" class="form-control" value="<?= e($settings['wallet_telegram_bot_token'] ?? '') ?>" placeholder="123456:ABC...">
                        </div>
                    </div>
                </div>

                <!-- HOME PAGE & ADS CONFIG -->
                <div class="col-12 mt-5 pt-3">
                    <h6 class="fw-bold mb-3 text-primary border-start border-4 border-primary ps-3">
                        <i class="fas fa-home me-2"></i>Cấu hình Trang chủ & Quảng cáo
                    </h6>
                    
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-none bg-light rounded-4">
                                <div class="card-body p-4">
                                    <label class="form-label fw-bold mb-1"><i class="fas fa-star text-warning me-2"></i>Sản Phẩm Tài Trợ / Cho Thuê</label>
                                    <p class="text-muted small mb-3">Sản phẩm có ID nhập ở đây sẽ hiển thị ở khu vực nổi bật nhất trang chủ.</p>
                                    
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-fingerprint"></i></span>
                                        <input type="text" name="sponsored_product_ids" class="form-control border-start-0 ps-0 fw-bold" value="<?= e($settings['sponsored_product_ids'] ?? '') ?>" placeholder="Ví dụ: 8, 3, 15, 22">
                                    </div>
                                    <div class="form-text mt-2 text-dark">
                                        <i class="fas fa-info-circle text-primary me-1"></i> Cách dùng: Nhập các ID sản phẩm cách nhau bằng dấu phẩy. 
                                        <span class="badge bg-white text-primary border ms-2">Khuyến nghị nhập 8 ID</span> (2 hàng, mỗi hàng 4 sản phẩm).
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Banner Management -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex align-items-center">
                                    <div class="bg-primary-subtle p-2 rounded-3 me-3"><i class="fas fa-ad text-primary"></i></div>
                                    <h6 class="fw-bold mb-0">Banner Trái (Sidebar Left)</h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Link ảnh</label>
                                        <input type="text" name="home_banner_left" class="form-control mb-2" value="<?= e($settings['home_banner_left'] ?? '') ?>" placeholder="https://...">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <hr class="flex-grow-1 my-0 opacity-25">
                                            <span class="small text-muted text-uppercase">Hoặc tải lên</span>
                                            <hr class="flex-grow-1 my-0 opacity-25">
                                        </div>
                                        <input type="file" name="home_banner_left_file" class="form-control form-control-sm" accept="image/*">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Link đích khi click</label>
                                        <input type="text" name="home_banner_left_link" class="form-control" value="<?= e($settings['home_banner_left_link'] ?? '') ?>" placeholder="Link Shopee, Lazada hoặc link website...">
                                    </div>
                                    <?php if (!empty($settings['home_banner_left'])): ?>
                                        <div class="mt-3 p-3 border rounded-4 bg-light text-center">
                                            <div class="small fw-bold text-muted mb-2 text-uppercase" style="font-size: 10px;">Xem trước Banner:</div>
                                            <div class="rounded-3 overflow-hidden shadow-sm mx-auto" style="max-width: 160px;">
                                                <img src="<?= $settings['home_banner_left'] ?>" class="img-fluid" style="max-height: 400px; object-fit: contain;">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex align-items-center">
                                    <div class="bg-primary-subtle p-2 rounded-3 me-3"><i class="fas fa-ad text-primary"></i></div>
                                    <h6 class="fw-bold mb-0">Banner Phải (Sidebar Right)</h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Link ảnh</label>
                                        <input type="text" name="home_banner_right" class="form-control mb-2" value="<?= e($settings['home_banner_right'] ?? '') ?>" placeholder="https://...">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <hr class="flex-grow-1 my-0 opacity-25">
                                            <span class="small text-muted text-uppercase">Hoặc tải lên</span>
                                            <hr class="flex-grow-1 my-0 opacity-25">
                                        </div>
                                        <input type="file" name="home_banner_right_file" class="form-control form-control-sm" accept="image/*">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Link đích khi click</label>
                                        <input type="text" name="home_banner_right_link" class="form-control" value="<?= e($settings['home_banner_right_link'] ?? '') ?>" placeholder="Link Shopee, Lazada hoặc link website...">
                                    </div>
                                    <?php if (!empty($settings['home_banner_right'])): ?>
                                        <div class="mt-3 p-3 border rounded-4 bg-light text-center">
                                            <div class="small fw-bold text-muted mb-2 text-uppercase" style="font-size: 10px;">Xem trước Banner:</div>
                                            <div class="rounded-3 overflow-hidden shadow-sm mx-auto" style="max-width: 160px;">
                                                <img src="<?= $settings['home_banner_right'] ?>" class="img-fluid" style="max-height: 400px; object-fit: contain;">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Hero Background -->
                        <div class="col-12">
                            <div class="card border-0 shadow-none bg-primary-subtle rounded-4">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-7">
                                            <label class="form-label fw-bold mb-1">Hero Background (Ảnh nền đầu trang chủ)</label>
                                            <p class="text-muted small mb-3">Thay thế màu tím mặc định bằng một tấm ảnh chuyên nghiệp hơn.</p>
                                            <input type="file" name="home_hero_bg_file" class="form-control border-primary shadow-none" accept="image/*">
                                        </div>
                                        <div class="col-md-5 text-center mt-3 mt-md-0">
                                            <?php if (!empty($settings['home_hero_bg'])): ?>
                                                <div class="position-relative d-inline-block">
                                                    <img src="<?= asset($settings['home_hero_bg']) ?>" class="rounded-3 shadow" style="height: 100px; width: 200px; object-fit: cover; border: 3px solid #fff;">
                                                    <div class="mt-2">
                                                        <input type="hidden" name="home_hero_bg" value="<?= e($settings['home_hero_bg']) ?>">
                                                        <div class="form-check d-inline-block">
                                                            <input class="form-check-input" type="checkbox" name="remove_home_hero_bg" value="1" id="remove_home_hero_bg">
                                                            <label class="form-check-label text-danger small fw-bold" for="remove_home_hero_bg">Xóa ảnh nền này</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="bg-white rounded-3 p-3 text-muted small border border-dashed">
                                                    <i class="fas fa-image fa-2x mb-2 opacity-25"></i>
                                                    <div>Chưa có ảnh nền riêng</div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Mode -->
                <div class="col-12 mt-5">
                    <div class="card border-danger bg-danger-subtle rounded-4">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-danger">Chế độ bảo trì</h6>
                                <p class="text-danger small mb-0 opacity-75">Khi bật, khách hàng sẽ không thể truy cập website.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="maintenance_mode" value="0">
                                <input class="form-check-input border-danger" type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? 0) ? 'checked' : '' ?> style="transform: scale(1.5);">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
                <span class="text-muted small italic">Lưu ý: Một số cài đặt có thể cần tải lại trang để áp dụng hoàn toàn.</span>
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                    <i class="fas fa-save me-2"></i> Lưu tất cả cài đặt
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.08); }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.05); }
.form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); border-color: #0d6efd; }
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Cài đặt hệ thống';
require_once __DIR__ . '/../layouts/admin.php';
?>
