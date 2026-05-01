<?php
$c = file_get_contents('d:/aicuatoi/app/Views/seller/order-detail.php');
$code = <<<EOT
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Lưu trạng thái
                    </button>
                </form>
                
                <?php if ($cur !== 'refunded'): ?>
                <hr>
                <div class="alert alert-info py-2 small mb-2">
                    <i class="fas fa-info-circle"></i> Nếu khách phàn nàn và bạn muốn hoàn lại tiền, bạn có thể tự thực hiện. Tiền sẽ hoàn ngay cho khách.
                </div>
                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#refundModal">
                    <i class="fas fa-undo"></i> Hoàn tiền cho khách (Refund)
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
EOT;
$search = <<<EOT
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Lưu trạng thái
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
EOT;

$modalCode = <<<EOT
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">

<?php if ($order['item_status'] !== 'refunded'): ?>
<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/seller/orders/' . \$order['order_id'] . '/refund') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="order_item_id" value="<?= \$order['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle"></i> Xác nhận hoàn tiền</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Khách hàng sẽ nhận lại <strong>100%</strong> số tiền đã thanh toán (<?= money(\$order['price'] * \$order['quantity']) ?>).</p>
                    <p>Số tiền bạn thực nhận (<strong><?= money(\$order['seller_amount']) ?></strong>) sẽ bị trừ khỏi ví của bạn (Ưu tiên trừ từ tiền đang bị tạm giữ của đơn này trước).</p>
                    <p>Sản phẩm (nếu có) sẽ được tự động đưa trở lại kho để bán tiếp.</p>
                    
                    <div class="mb-3 mt-4">
                        <label class="form-label fw-bold">Ghi chú cho khách (Lý do hoàn)</label>
                        <textarea name="seller_note" class="form-control" rows="3" required placeholder="Ví dụ: Rất xin lỗi, sản phẩm bị lỗi do NSX, tôi xin hoàn tiền lại..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Hành động này KHÔNG THỂ hoàn tác. Bạn chắc chắn chứ?')">Đồng ý Hoàn tiền</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
EOT;
$modalSearch = <<<EOT
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
EOT;

$c = str_replace($search, $code, $c);
$c = str_replace($modalSearch, $modalCode, $c);
file_put_contents('d:/aicuatoi/app/Views/seller/order-detail.php', $c);
