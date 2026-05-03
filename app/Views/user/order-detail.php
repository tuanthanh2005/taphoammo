<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-box"></i> Chi tiết đơn hàng #<?= e($order['order_code']) ?></h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Mã đơn hàng:</strong> <?= e($order['order_code']) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Ngày đặt:</strong> <?= Helper::formatDate($order['created_at']) ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Trạng thái thanh toán:</strong>
                            <?php if ($order['payment_status'] === 'paid'): ?>
                                <span class="badge bg-success">Đã thanh toán</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Chờ thanh toán</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Trạng thái đơn:</strong>
                            <?php if ($order['order_status'] === 'completed'): ?>
                                <span class="badge bg-success">Hoàn thành</span>
                            <?php else: ?>
                                <span class="badge bg-info">Đang xử lý</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Sản phẩm đã mua</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6><?= e($item['product_name']) ?></h6>
                                    <p class="text-muted mb-1 d-flex align-items-center gap-2">
                                        <i class="fas fa-user"></i> Seller: 
                                        <a href="<?= url('/seller/' . e($item['seller_username'])) ?>" class="fw-bold text-decoration-none text-primary"><?= e($item['seller_name'] ?: $item['seller_username']) ?></a>
                                        <button onclick="event.stopPropagation(); _iwOpenChat(<?= $item['seller_id'] ?>, '<?= e($item['seller_name'] ?: $item['seller_username']) ?>')" class="btn btn-sm btn-link p-0 text-decoration-none" title="Nhắn tin cho người bán">
                                            <i class="fas fa-comment-dots"></i> Nhắn tin
                                        </button>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Giá:</strong> <?= money($item['price']) ?> x <?= $item['quantity'] ?>
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <strong class="text-success"><?= money($item['subtotal']) ?></strong>
                                </div>
                            </div>

                            <?php if ($order['payment_status'] === 'paid' && !empty($item['stocks'])): ?>
                                <div class="alert alert-success mt-3">
                                    <strong><i class="fas fa-key"></i> Nội dung sản phẩm:</strong>
                                    <div class="mt-2">
                                        <?php foreach ($item['stocks'] as $stock): ?>
                                            <?php $parsedStock = Helper::parseStockContent($stock['content']); ?>
                                            <div class="bg-white p-2 rounded mb-2 text-dark">
                                                <?php if ($parsedStock['type'] === 'file'): ?>
                                                    <a href="<?= e($parsedStock['download_url']) ?>" target="_blank"
                                                        class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-download me-1"></i> <?= e($parsedStock['display_text']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <code><?= e($parsedStock['display_text']) ?></code>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php
                            $statusMap = [
                                'processing' => ['warning', '⏳ Đang xử lý'],
                                'delivered' => ['success', '✅ Đã giao hàng'],
                                'issue' => ['danger', '⚠️ Có vấn đề - Liên hệ seller'],
                                'refunded' => ['secondary', '↩️ Đã hoàn tiền'],
                            ];
                            $itemStatus = $item['item_status'] ?? 'processing';
                            [$sColor, $sLabel] = $statusMap[$itemStatus] ?? ['info', $itemStatus];
                            ?>
                            <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-bold small">Trạng thái từ seller:</span>
                                <span class="badge bg-<?= $sColor ?> px-3 py-2"><?= $sLabel ?></span>
                                <?php if ($item['status_updated_at']): ?>
                                    <small class="text-muted">·
                                        <?= date('d/m/Y H:i', strtotime($item['status_updated_at'])) ?></small>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($item['seller_note'])): ?>
                                <div class="alert alert-info mt-2 py-2 mb-0">
                                    <i class="fas fa-comment-dots"></i> <strong>Ghi chú từ seller:</strong>
                                    <?= nl2br(e($item['seller_note'])) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Đánh giá sản phẩm -->
                            <div class="mt-3 pt-3 border-top">
                                <?php if (isset($reviewsByProduct[$item['product_id']])): ?>
                                    <?php $review = $reviewsByProduct[$item['product_id']]; ?>
                                    <div class="bg-light p-3 rounded">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold small"><i class="fas fa-check-circle text-success"></i> Bạn đã
                                                đánh giá:</span>
                                            <div class="text-warning">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?= $i <= $review['rating'] ? '' : '-o' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($review['comment'])): ?>
                                            <p class="small text-muted mb-0 fst-italic">"<?= e($review['comment']) ?>"</p>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($item['item_status'] === 'delivered'): ?>
                                    <button class="btn btn-sm btn-outline-warning rounded-pill px-3" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#reviewForm_<?= $item['id'] ?>">
                                        <i class="fas fa-star me-1"></i> Đánh giá sản phẩm
                                    </button>
                                    <div class="collapse mt-3" id="reviewForm_<?= $item['id'] ?>">
                                        <form action="<?= url('/user/orders/' . $order['id'] . '/review') ?>" method="POST"
                                            class="bg-light p-3 rounded shadow-sm border">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold mb-1">Chọn số sao:</label>
                                                <div class="rating-stars d-flex flex-row-reverse justify-content-end gap-1">
                                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                                        <input type="radio" name="rating" value="<?= $i ?>"
                                                            id="star_<?= $item['id'] ?>_<?= $i ?>" class="btn-check" <?= $i === 5 ? 'checked' : '' ?>>
                                                        <label for="star_<?= $item['id'] ?>_<?= $i ?>"
                                                            class="btn btn-sm btn-outline-warning border-0 p-0 fs-5">
                                                            <i class="far fa-star"></i>
                                                        </label>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <textarea name="comment" class="form-control" rows="2"
                                                    placeholder="Nhận xét của bạn về sản phẩm..."></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-warning btn-sm px-4 fw-bold rounded-pill">
                                                <i class="fas fa-paper-plane me-1"></i> Gửi đánh giá
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Tổng đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <strong><?= money($order['total_amount']) ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Tổng cộng:</strong>
                        <strong class="text-success fs-4"><?= money($order['total_amount']) ?></strong>
                    </div>
                </div>
            </div>

            <div class="card mt-4 border-warning">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="fas fa-balance-scale"></i> Trung tâm hỗ trợ & Khiếu nại
                </div>
                <div class="card-body">
                    <?php
                    $eligibleDisputeItems = [];
                    $activeDisputeItems = [];
                    foreach ($disputes as $d) {
                        if (in_array($d['status'], ['open', 'under_review'], true)) {
                            $activeDisputeItems[(int) ($d['order_item_id'] ?? 0)] = $d;
                        }
                    }

                    $minimumDisputeHours = Helper::getMinimumDisputeHours();
                    foreach ($order['items'] as $item) {
                        $itemId = (int) $item['id'];
                        $wDays = (int) ($item['warranty_days'] ?? 0);
                        if (($item['item_status'] ?? '') === 'refunded') {
                            continue;
                        }

                        if (isset($activeDisputeItems[$itemId])) {
                            continue;
                        }

                        if (($disputeCountsByItem[$itemId] ?? 0) >= 2) {
                            continue;
                        }

                        $startAt = $order['created_at'];
                        if (in_array($item['item_status'] ?? '', ['delivered', 'disputed', 'issue', 'refunded', 'released'], true) && !empty($item['status_updated_at'])) {
                            $startAt = $item['status_updated_at'];
                        }

                        $protectionSeconds = max($minimumDisputeHours * 3600, $wDays * 86400);
                        $expiresAt = strtotime($startAt) + $protectionSeconds;
                        if (time() <= $expiresAt) {
                            $item['_dispute_expires_at'] = $expiresAt;
                            $eligibleDisputeItems[] = $item;
                        }
                    }
                    ?>

                    <?php if ($order['payment_status'] !== 'paid'): ?>
                        <p class="text-muted small mb-0">Vui lòng thanh toán đơn hàng để có thể sử dụng tính năng khiếu nại.
                        </p>
                    <?php elseif (!empty($eligibleDisputeItems)): ?>
                        <p class="small text-muted mb-2">Mỗi sản phẩm có tối đa 2 lần khiếu nại. Sản phẩm không bảo hành vẫn
                            có cửa sổ tranh chấp tối thiểu <?= (int) $minimumDisputeHours ?>h tính từ lúc seller giao hàng
                            thực tế.</p>
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal"
                            data-bs-target="#disputeModal">
                            <i class="fas fa-flag"></i> Báo lỗi / Khiếu nại
                        </button>
                    <?php elseif (!empty($activeDisputeItems)): ?>
                        <div class="alert alert-danger mb-0">
                            <h6 class="alert-heading fw-bold"><i class="fas fa-exclamation-triangle"></i> Có sản phẩm đang
                                khiếu nại</h6>
                            <p class="small mb-2">Một hoặc nhiều sản phẩm trong đơn đang được Admin xử lý khiếu nại. Bạn có
                                thể theo dõi trong trang tranh chấp.</p>
                            <a href="<?= url('/user/disputes') ?>" class="btn btn-sm btn-danger">Xem trạng thái</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary mb-0">
                            <i class="fas fa-clock"></i> Hiện không còn sản phẩm nào trong đơn còn thời hạn khiếu nại hợp
                            lệ.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($disputes)): ?>
                <div class="card mt-3">
                    <div class="card-header fw-bold small">Lịch sử khiếu nại</div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($disputes as $d): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="small">#<?= e($d['dispute_code']) ?></span>
                                <?php
                                $sMap = ['open' => 'danger', 'under_review' => 'warning', 'resolved_refund' => 'success', 'resolved_partial' => 'info', 'resolved_rejected' => 'secondary', 'closed' => 'dark'];
                                ?>
                                <span class="badge bg-<?= $sMap[$d['status']] ?? 'primary' ?> font-monospace"
                                    style="font-size:0.65rem">
                                    <?= strtoupper($d['status']) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <a href="<?= url('/user/orders') ?>" class="btn btn-outline-secondary w-100 mt-3">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>
</div>

<!-- Dispute Modal -->
<div class="modal fade" id="disputeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= url('/user/orders/' . $order['id'] . '/dispute') ?>" method="POST"
            enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-flag me-2"></i> Gửi khiếu nại đơn hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-info-circle"></i> Khiếu nại sẽ được Admin xem xét. Tiền sẽ được tạm giữ an toàn
                        cho đến khi có kết quả.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Sản phẩm cần khiếu nại</label>
                        <select class="form-select" name="order_item_id" required>
                            <?php foreach ($eligibleDisputeItems as $item): ?>
                                <option value="<?= (int) $item['id'] ?>">
                                    <?= e($item['product_name']) ?> -
                                    <?= (int) ($item['warranty_days'] ?? 0) > 0 ? 'Bảo hành: ' . e(Helper::formatWarranty($item['warranty_days'] ?? 0)) : 'Tranh chấp tối thiểu ' . (int) $minimumDisputeHours . 'h' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Lý do khiếu nại</label>
                        <select class="form-select" name="reason" required>
                            <option value="not_received">Chưa nhận được hàng</option>
                            <option value="wrong_item">Sai sản phẩm / Không đúng mô tả</option>
                            <option value="not_working">Sản phẩm lỗi / Không hoạt động</option>
                            <option value="scam">Có dấu hiệu lừa đảo</option>
                            <option value="other">Lý do khác</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Chi tiết vấn đề</label>
                        <textarea class="form-control" name="description" rows="4" required
                            placeholder="Mô tả chi tiết lỗi để Admin dễ dàng xử lý cho bạn..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ảnh bằng chứng (Tối đa 3 ảnh)</label>
                        <input type="file" class="form-control" name="evidence_images[]" multiple accept="image/*">
                        <div class="form-text small">Ảnh chụp màn hình lỗi hoặc bằng chứng liên quan.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="fas fa-paper-plane me-1"></i> Gửi khiếu nại ngay
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .rating-stars input:checked~label i,
    .rating-stars label:hover~label i,
    .rating-stars label:hover i {
        color: #ffc107 !important;
    }

    .rating-stars input:checked~label i:before,
    .rating-stars label:hover~label i:before,
    .rating-stars label:hover i:before {
        content: "\f005";
        font-weight: 900;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>