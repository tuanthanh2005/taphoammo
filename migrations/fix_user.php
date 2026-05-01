<?php
$c = file_get_contents('d:/aicuatoi/app/Views/user/order-detail.php');
$code = <<<EOT
            <a href="<?= url('/user/orders') ?>" class="btn btn-outline-secondary w-100 mt-3">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>

            <?php
            require_once __DIR__ . '/../../Models/Dispute.php';
            \$disputeModel = new Dispute();
            \$hasOpenDispute = \$disputeModel->hasOpenDispute(\$order['id'], Auth::id());
            ?>

            <?php if (\$hasOpenDispute): ?>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i> Đơn hàng này đang có khiếu nại chưa xử lý. Admin sẽ kiểm tra và phản hồi sớm nhất.
                </div>
            <?php else:
                // Get days since order
                \$daysSinceOrder = (time() - strtotime(\$order['created_at'])) / (60 * 60 * 24);
                if (\$daysSinceOrder <= 7 && \$order['payment_status'] === 'paid'): // Can dispute within 7 days
            ?>
                <button type="button" class="btn btn-outline-danger w-100 mt-3" data-bs-toggle="modal" data-bs-target="#disputeModal">
                    <i class="fas fa-exclamation-circle"></i> Báo cáo / Khiếu nại đơn hàng
                </button>
            <?php endif; endif; ?>
        </div>
    </div>
</div>

<!-- Dispute Modal -->
<div class="modal fade" id="disputeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/user/orders/' . \$order['id'] . '/dispute') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Báo cáo đơn hàng #<?= e(\$order['order_code']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tiền của đơn hàng này đang được tạm giữ. Hãy gửi bằng chứng để Admin xem xét và hoàn tiền cho bạn.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lý do khiếu nại</label>
                        <select name="reason" class="form-select" required>
                            <option value="">-- Chọn lý do --</option>
                            <option value="not_working">Sản phẩm bị lỗi / Không hoạt động</option>
                            <option value="wrong_item">Sai sản phẩm / Không đúng mô tả</option>
                            <option value="not_received">Chưa nhận được sản phẩm</option>
                            <option value="scam">Nghi ngờ lừa đảo</option>
                            <option value="other">Lý do khác</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mô tả chi tiết</label>
                        <textarea name="description" class="form-control" rows="4" required placeholder="Vui lòng mô tả chi tiết vấn đề bạn gặp phải..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Bằng chứng (Hình ảnh)</label>
                        <input type="file" name="evidence_images[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">Tối đa 3 ảnh. Chụp màn hình lỗi, tin nhắn với người bán...</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Gửi khiếu nại</button>
                </div>
            </form>
        </div>
    </div>
</div>
EOT;
$search = <<<EOT
            <a href="<?= url('/user/orders') ?>" class="btn btn-outline-secondary w-100 mt-3">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>
</div>
EOT;
$c = str_replace($search, $code, $c);
file_put_contents('d:/aicuatoi/app/Views/user/order-detail.php', $c);
