<?php 
ob_start();
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-cog"></i> CÃ i Ä‘áº·t há»‡ thá»‘ng</h5>
    </div>
    <div class="card-body">
        <form action="<?= url('/admin/settings/update') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            
            <h6 class="border-bottom pb-2 mb-3">ThÃ´ng tin website</h6>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">TÃªn website</label>
                        <input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? 'AI CỦA TÔI') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Email liÃªn há»‡</label>
                        <input type="email" name="site_email" class="form-control" value="<?= e($settings['site_email'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Sá»‘ Ä‘iá»‡n thoáº¡i</label>
                <input type="text" name="site_phone" class="form-control" value="<?= e($settings['site_phone'] ?? '') ?>">
            </div>
            
            <h6 class="border-bottom pb-2 mb-3 mt-4">Cáº¥u hÃ¬nh phÃ­</h6>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">PhÃ­ admin trÃªn Ä‘Æ¡n hÃ ng (%)</label>
                        <input type="number" name="admin_order_fee_percent" class="form-control" value="<?= e($settings['admin_order_fee_percent'] ?? 5) ?>" min="0" max="100" step="0.1">
                        <small class="text-muted">Máº·c Ä‘á»‹nh: 5%</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">PhÃ­ rÃºt tiá»n seller (%)</label>
                        <input type="number" name="seller_withdraw_fee_percent" class="form-control" value="<?= e($settings['seller_withdraw_fee_percent'] ?? 5) ?>" min="0" max="100" step="0.1">
                        <small class="text-muted">Máº·c Ä‘á»‹nh: 5%</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Sá»‘ tiá»n rÃºt tá»‘i thiá»ƒu (VNÄ)</label>
                        <input type="number" name="min_withdraw_amount" class="form-control" value="<?= e($settings['min_withdraw_amount'] ?? 50000) ?>" min="0" step="1000">
                        <small class="text-muted">Máº·c Ä‘á»‹nh: 50,000Ä‘</small>
                    </div>
                </div>
            </div>
            
            <h6 class="border-bottom pb-2 mb-3 mt-4">Cáº¥u hÃ¬nh chá»©c nÄƒng</h6>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="enable_product_approval" value="0">
                            <input class="form-check-input" type="checkbox" name="enable_product_approval" value="1" <?= ($settings['enable_product_approval'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label">Báº­t duyá»‡t sáº£n pháº©m seller</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="enable_google_login" value="0">
                            <input class="form-check-input" type="checkbox" name="enable_google_login" value="1" <?= ($settings['enable_google_login'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label">Báº­t Ä‘Äƒng nháº­p Google</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="enable_affiliate" value="0">
                            <input class="form-check-input" type="checkbox" name="enable_affiliate" value="1" <?= ($settings['enable_affiliate'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label">Báº­t há»‡ thá»‘ng affiliate</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Hoa há»“ng affiliate (%)</label>
                <input type="number" name="affiliate_commission_percent" class="form-control" value="<?= e($settings['affiliate_commission_percent'] ?? 5) ?>" min="0" max="100" step="0.1">
                <small class="text-muted">Pháº§n trÄƒm hoa há»“ng cho ngÆ°á»i giá»›i thiá»‡u</small>
            </div>
            
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="maintenance_mode" value="0">
                    <input class="form-check-input" type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label text-danger">Báº­t cháº¿ Ä‘á»™ báº£o trÃ¬ website</label>
                </div>
            </div>

            <h6 class="border-bottom pb-2 mb-3 mt-4">C?u hình n?p ti?n</h6>

            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Mã ngân hàng VietQR</label>
                        <input type="text" name="deposit_bank_code" class="form-control" value="<?= e($settings['deposit_bank_code'] ?? 'mb') ?>" placeholder="mb">
                        <small class="text-muted">Ví d?: mb, vietinbank, acb...</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Tên ngân hàng</label>
                        <input type="text" name="deposit_bank_name" class="form-control" value="<?= e($settings['deposit_bank_name'] ?? 'MB Bank') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Ch? tài kho?n</label>
                        <input type="text" name="deposit_account_name" class="form-control" value="<?= e($settings['deposit_account_name'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">S? tài kho?n</label>
                        <input type="text" name="deposit_account_number" class="form-control" value="<?= e($settings['deposit_account_number'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Telegram username h? tr?</label>
                        <input type="text" name="telegram_support_username" class="form-control" value="<?= e($settings['telegram_support_username'] ?? '@specademy') ?>" placeholder="@specademy">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Telegram link h? tr?</label>
                        <input type="text" name="telegram_support_url" class="form-control" value="<?= e($settings['telegram_support_url'] ?? 'https://t.me/specademy') ?>" placeholder="https://t.me/specademy">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Telegram Chat ID nh?n thông báo</label>
                        <input type="text" name="telegram_chat_id" class="form-control" value="<?= e($settings['telegram_chat_id'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Telegram Bot Token</label>
                <input type="text" name="telegram_bot_token" class="form-control" value="<?= e($settings['telegram_bot_token'] ?? '') ?>" placeholder="123456:ABC...">
                <small class="text-muted">Dùng d? g?i thông báo khi user b?m xác nh?n dã chuy?n kho?n.</small>
            </div>
            <h6 class="border-bottom pb-2 mb-3 mt-4">Giao diá»‡n Trang chá»§</h6>
            <div class="mb-3">
                <label class="form-label">áº¢nh ná»n Banner chÃ­nh (Hero Background)</label>
                <input type="file" name="home_hero_bg_file" class="form-control" accept="image/*">
                <?php if (!empty($settings['home_hero_bg'])): ?>
                    <div class="mt-2">
                        <img src="<?= asset($settings['home_hero_bg']) ?>" alt="Current Hero BG" style="max-height: 100px; border-radius: 5px;">
                        <input type="hidden" name="home_hero_bg" value="<?= e($settings['home_hero_bg']) ?>">
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="remove_home_hero_bg" value="1" id="remove_home_hero_bg">
                            <label class="form-check-label text-danger" for="remove_home_hero_bg">
                                XÃ³a áº£nh ná»n nÃ y (quay vá» mÃ u tÃ­m máº·c Ä‘á»‹nh)
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
                <small class="text-muted d-block mt-1">Chá»n áº£nh tá»« mÃ¡y tÃ­nh Ä‘á»ƒ thay tháº¿ mÃ u ná»n tÃ­m máº·c Ä‘á»‹nh.</small>
            </div>
            
            <div class="mb-3">
                <label class="form-label">ID Sáº£n Pháº©m TÃ i Trá»£ / Cho ThuÃª (Sáº½ hiá»‡n thay tháº¿ pháº§n "Vá»«a Má»›i ÄÄƒng BÃ¡n")</label>
                <input type="text" name="sponsored_product_ids" class="form-control" value="<?= e($settings['sponsored_product_ids'] ?? '') ?>" placeholder="VÃ­ dá»¥: 12, 45, 8, 23">
                <small class="text-muted">Nháº­p cÃ¡c ID sáº£n pháº©m cÃ¡ch nhau báº±ng dáº¥u pháº©y. Khuyáº¿n nghá»‹ nháº­p 8 ID (2 hÃ ng, má»—i hÃ ng 4 sáº£n pháº©m).</small>
            </div>

            <h6 class="border-bottom pb-2 mb-3 mt-4">Quáº£n lÃ½ Quáº£ng CÃ¡o (Banner 2 bÃªn)</h6>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Link áº£nh Banner TrÃ¡i</label>
                        <input type="text" name="home_banner_left" class="form-control" value="<?= e($settings['home_banner_left'] ?? '') ?>" placeholder="https://example.com/banner-left.jpg">
                        <small class="text-muted">KÃ­ch thÆ°á»›c chuáº©n: 160x600 px. Äá»ƒ trá»‘ng sáº½ hiá»‡n "LiÃªn há»‡ Ä‘áº·t quáº£ng cÃ¡o"</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link Ä‘Ã­ch khi click Banner TrÃ¡i</label>
                        <input type="text" name="home_banner_left_link" class="form-control" value="<?= e($settings['home_banner_left_link'] ?? '') ?>" placeholder="https://shopee.vn/shop/123">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Link áº£nh Banner Pháº£i</label>
                        <input type="text" name="home_banner_right" class="form-control" value="<?= e($settings['home_banner_right'] ?? '') ?>" placeholder="https://example.com/banner-right.jpg">
                        <small class="text-muted">KÃ­ch thÆ°á»›c chuáº©n: 160x600 px. Äá»ƒ trá»‘ng sáº½ hiá»‡n "LiÃªn há»‡ Ä‘áº·t quáº£ng cÃ¡o"</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link Ä‘Ã­ch khi click Banner Pháº£i</label>
                        <input type="text" name="home_banner_right_link" class="form-control" value="<?= e($settings['home_banner_right_link'] ?? '') ?>" placeholder="https://lazada.vn/shop/abc">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> LÆ°u cÃ i Ä‘áº·t
            </button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'CÃ i Ä‘áº·t há»‡ thá»‘ng';
require_once __DIR__ . '/../layouts/admin.php';
?>

