<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="product-detail-page bg-light pb-5">
    <!-- Breadcrumbs -->
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-decoration-none text-muted">Trang
                        chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/category/' . $product['category_slug']) ?>"
                        class="text-decoration-none text-muted"><?= e($product['category_name']) ?></a></li>
                <li class="breadcrumb-item active text-dark fw-medium" aria-current="page"><?= e($product['name']) ?>
                </li>
            </ol>
        </nav>
    </div>

    <div class="container">
        <div class="row g-4">
            <!-- Cột trái: Hình ảnh -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 15px;">
                    <div class="product-image-container position-relative bg-white p-2">
                        <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>"
                            class="img-fluid w-100 main-product-image" alt="<?= e($product['name']) ?>"
                            style="border-radius: 12px; object-fit: contain; max-height: 500px;">

                        <?php if ($product['is_featured']): ?>
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm fw-bold">
                                    <i class="fas fa-crown"></i> NỔI BẬT
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Trust Badges -->
                <div class="row g-2 mt-3">
                    <div class="col-6">
                        <div
                            class="d-flex align-items-center p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success">
                            <i class="fas fa-shield-alt text-success fs-4 me-3"></i>
                            <div>
                                <div class="fw-bold small">Bảo hành</div>
                                <div class="text-muted" style="font-size: 0.75rem;">An tâm sử dụng</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div
                            class="d-flex align-items-center p-3 bg-white rounded-3 shadow-sm border-start border-4 border-primary">
                            <i class="fas fa-bolt text-primary fs-4 me-3"></i>
                            <div>
                                <div class="fw-bold small">Giao ngay</div>
                                <div class="text-muted" style="font-size: 0.75rem;">Tự động 24/7</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Thông tin & Mua hàng -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm p-4" style="border-radius: 15px;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-success-soft text-success px-3 py-1 rounded-pill mb-2"
                            style="font-size: 0.75rem;">
                            <i class="fas fa-tag me-1"></i> <?= e($product['category_name']) ?>
                        </span>
                        <div class="product-actions">
                            <button class="btn btn-light btn-sm rounded-circle shadow-sm" title="Nhắn tin với shop" onclick="openChatModal(<?= $product['seller_id'] ?>, '<?= e($product['seller_name']) ?>')">
                                <i class="fas fa-comments text-primary"></i>
                            </button>
                        </div>
                    </div>

                    <h1 class="h3 fw-bold text-dark mb-3"><?= e($product['name']) ?></h1>

                    <!-- Rating & Sold -->
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                        <div class="me-4 pe-4 border-end">
                            <?php if (!empty($product['rating_avg']) && $product['rating_avg'] > 0): ?>
                                <div class="text-warning mb-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= $product['rating_avg'] ? '' : '-o' ?>"></i>
                                    <?php endfor; ?>
                                    <span
                                        class="text-dark fw-bold ms-1"><?= number_format($product['rating_avg'], 1) ?></span>
                                </div>
                                <div class="text-muted small"><?= $product['rating_count'] ?> đánh giá</div>
                            <?php else: ?>
                                <div class="text-muted mb-1"><i class="far fa-star"></i> <i class="far fa-star"></i> <i
                                        class="far fa-star"></i> <i class="far fa-star"></i> <i class="far fa-star"></i>
                                </div>
                                <div class="text-muted small">Chưa có đánh giá</div>
                            <?php endif; ?>
                        </div>
                        <div class="me-4 pe-4 border-end">
                            <div class="fw-bold text-dark mb-1"><?= $product['total_sold'] ?? 0 ?></div>
                            <div class="text-muted small">Đã bán</div>
                        </div>
                        <div>
                            <div class="fw-bold text-dark mb-1"><?= $product['stock_quantity'] ?></div>
                            <div class="text-muted small">Còn lại</div>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="bg-light p-4 rounded-4 mb-4">
                        <div class="d-flex align-items-center flex-wrap">
                            <?php if ($product['sale_price']): ?>
                                <h2 class="text-danger fw-bold mb-0 me-3 fs-1"><?= money($product['sale_price']) ?></h2>
                                <span
                                    class="text-muted text-decoration-line-through fs-5"><?= money($product['price']) ?></span>
                                <span class="badge bg-danger ms-3 px-2 py-1" style="font-size: 0.8rem;">
                                    -<?= round((($product['price'] - $product['sale_price']) / $product['price']) * 100) ?>%
                                </span>
                            <?php else: ?>
                                <h2 class="text-primary fw-bold mb-0 fs-1"><?= money($product['price']) ?></h2>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Seller Info Mini -->
                    <div class="d-flex align-items-center mb-4 p-3 border rounded-3 bg-white shadow-sm">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4"
                                style="width: 50px; height: 50px;">
                                <?= mb_strtoupper(mb_substr($product['seller_name'] ?? 'U', 0, 1)) ?>
                            </div>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="fw-bold text-dark d-flex align-items-center">
                                <?= e($product['seller_name']) ?>
                                <i class="fas fa-check-circle text-primary ms-1" style="font-size: 0.8rem;"
                                    title="Đã xác minh"></i>
                            </div>
                            <div class="text-muted small">Người bán chuyên nghiệp</div>
                        </div>
                        <div class="ms-auto">
                            <a href="<?= url('/seller/' . ($product['seller_username'] ?? $product['seller_id'])) ?>"
                                class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                Xem Shop
                            </a>
                        </div>
                    </div>

                    <!-- Short Description -->
                    <?php if (!empty($product['short_description'])): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark mb-2">Ưu đãi nổi bật:</h6>
                            <div class="text-secondary small line-height-lg">
                                <?= nl2br(e($product['short_description'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Buy Section -->
                    <?php if (Auth::check()): ?>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <div class="mt-4">
                                <div class="d-flex align-items-center mb-4">
                                    <label class="fw-bold text-dark me-3">Số lượng:</label>
                                    <div class="input-group" style="width: 140px;">
                                        <button class="btn btn-outline-secondary border-end-0 px-3" type="button"
                                            onclick="this.nextElementSibling.stepDown()"><i class="fas fa-minus"></i></button>
                                        <input type="number" id="page_quantity" name="quantity"
                                            class="form-control text-center fw-bold border-start-0 border-end-0" value="1"
                                            min="1" max="<?= $product['stock_quantity'] ?>">
                                        <button class="btn btn-outline-secondary border-start-0 px-3" type="button"
                                            onclick="this.previousElementSibling.stepUp()"><i class="fas fa-plus"></i></button>
                                    </div>
                                    <div class="ms-3 text-muted small">Có sẵn: <?= $product['stock_quantity'] ?></div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-12">
                                        <button type="button"
                                            class="btn btn-success btn-lg w-100 py-3 rounded-3 fw-bold d-flex align-items-center justify-content-center shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#buyNowModal" onclick="openBuyNowModal()">
                                            <i class="fas fa-bolt me-2"></i> MUA NGAY
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                                <div class="alert alert-warning py-3 rounded-3 mt-4 d-flex align-items-center">
                                    <i class="fas fa-exclamation-circle fs-4 me-3"></i>
                                    <div class="fw-bold">Sản phẩm hiện đang tạm hết hàng! Hãy quay lại sau.</div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="mt-4 p-4 rounded-4 text-center"
                                style="background-color: #f0f7ff; border: 1px dashed #0d6efd;">
                                <div class="fs-4 mb-2 text-primary"><i class="fas fa-user-lock"></i></div>
                                <h6 class="fw-bold text-dark mb-2">Đăng nhập để mua hàng</h6>
                                <p class="text-muted small mb-3">Vui lòng đăng nhập tài khoản của bạn để tiến hành thanh
                                    toán sản phẩm này.</p>
                                <a href="<?= url('/login') ?>" class="btn btn-primary rounded-pill px-5 fw-bold shadow">
                                    Đăng Nhập Ngay
                                </a>
                            </div>
                        <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Buy Now Modal -->
        <div class="modal fade" id="buyNowModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold">Xác nhận đặt hàng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?= url('/checkout/instant') ?>" method="POST" id="instantPurchaseForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <div class="modal-body">
                            <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-3">
                                <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>"
                                    class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                <div>
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 250px;">
                                        <?= e($product['name']) ?></div>
                                    <div class="text-danger fw-bold">
                                        <?= money($product['sale_price'] ?? $product['price']) ?></div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Số lượng muốn
                                    mua</label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <button class="btn btn-outline-secondary border-end-0 px-4" type="button"
                                        onclick="updateModalQty(-1)"><i class="fas fa-minus"></i></button>
                                    <input type="number" name="quantity" id="modal_quantity"
                                        class="form-control text-center fw-bold border-start-0 border-end-0" value="1"
                                        min="1" max="<?= $product['stock_quantity'] ?>" onchange="updateModalTotal()">
                                    <button class="btn btn-outline-secondary border-start-0 px-4" type="button"
                                        onclick="updateModalQty(1)"><i class="fas fa-plus"></i></button>
                                </div>
                                <div class="text-end mt-2 small text-muted">Còn lại: <?= $product['stock_quantity'] ?>
                                    sản phẩm</div>
                            </div>

                            <?php if (!empty($product['require_note'])): ?>
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Ghi Chú <span class="text-danger">*</span></label>
                                <textarea name="note" id="modal_note" class="form-control" rows="2" placeholder="Sản phẩm này bắt buộc nhập ghi chú (Ví dụ: Email cần nâng cấp, link profile...)" required></textarea>
                            </div>
                            <?php endif; ?>

                            <div class="p-3 rounded-3" style="background-color: #fff9f0; border: 1px solid #ffeeba;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark">Tổng thanh toán:</span>
                                    <span class="fs-4 fw-bold text-danger"
                                        id="modal_total_price"><?= money($product['sale_price'] ?? $product['price']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                            <button type="button" class="btn btn-light rounded-pill px-4"
                                data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow">Xác nhận đặt
                                hàng ngay</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            function openBuyNowModal() {
                const pageQty = document.getElementById('page_quantity');
                const modalQty = document.getElementById('modal_quantity');

                if (pageQty && modalQty) {
                    modalQty.value = pageQty.value || 1;
                    updateModalTotal();
                }
            }

            function updateModalQty(change) {
                const input = document.getElementById('modal_quantity');
                let val = parseInt(input.value || 1, 10) + change;
                if (val < 1) val = 1;
                if (val > <?= $product['stock_quantity'] ?>) val = <?= $product['stock_quantity'] ?>;
                input.value = val;
                updateModalTotal();
            }

            function updateModalTotal() {
                const qty = parseInt(document.getElementById('modal_quantity').value || 1, 10);
                const price = <?= $product['sale_price'] ?? $product['price'] ?>;
                const total = qty * price;

                const formatter = new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND',
                    minimumFractionDigits: 0
                });

                let totalStr = formatter.format(total).replace('₫', 'đ').trim();
                document.getElementById('modal_total_price').innerText = totalStr;
            }

            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('instantPurchaseForm');
                if (!form) return;

                form.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    if (!form.reportValidity()) {
                        return;
                    }

                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalButtonHtml = submitButton ? submitButton.innerHTML : '';

                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = 'Dang xu ly...';
                    }

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: new FormData(form)
                        });

                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                                return;
                            }

                            throw new Error(data.message || 'Dat hang that bai');
                        }

                        const modalElement = document.getElementById('buyNowModal');
                        const modalInstance = bootstrap.Modal.getInstance(modalElement);
                        if (modalInstance) {
                            modalInstance.hide();
                        }

                        form.reset();
                        const pageQty = document.getElementById('page_quantity');
                        if (pageQty) {
                            pageQty.value = 1;
                        }
                        const modalQty = document.getElementById('modal_quantity');
                        if (modalQty) {
                            modalQty.value = 1;
                        }
                        updateModalTotal();

                        Swal.fire({
                            icon: 'success',
                            title: 'Thanh cong!',
                            text: data.message,
                            confirmButtonColor: '#198754'
                        });
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Co loi xay ra',
                            text: error.message || 'Khong the dat hang',
                            confirmButtonColor: '#dc3545'
                        });
                    } finally {
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.innerHTML = originalButtonHtml;
                        }
                    }
                });
            });
        </script>

        <!-- Tabs Content -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 15px;">
                    <ul class="nav nav-pills bg-white p-2 border-bottom" id="productTabs" role="tablist">
                        <li class="nav-item flex-fill text-center">
                            <a class="nav-link active fw-bold py-3" data-bs-toggle="pill" href="#description">
                                <i class="fas fa-file-alt me-2"></i>Mô tả chi tiết
                            </a>
                        </li>
                        <li class="nav-item flex-fill text-center">
                            <a class="nav-link fw-bold py-3" data-bs-toggle="pill" href="#reviews">
                                <i class="fas fa-star me-2"></i>Đánh giá (<?= count($reviews) ?>)
                            </a>
                        </li>
                        <li class="nav-item flex-fill text-center">
                            <a class="nav-link fw-bold py-3" data-bs-toggle="pill" href="#warranty">
                                <i class="fas fa-shield-check me-2"></i>Chính sách bảo hành
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-4 p-md-5">
                        <div id="description" class="tab-pane fade show active">
                            <div class="product-description-content line-height-lg text-secondary">
                                <?= nl2br(e($product['description'] ?? 'Chưa có mô tả chi tiết cho sản phẩm này.')) ?>
                            </div>
                        </div>

                        <div id="reviews" class="tab-pane fade">
                            <?php if (!empty($reviews)): ?>
                                <div class="row g-4">
                                    <?php foreach ($reviews as $review): ?>
                                        <div class="col-md-6">
                                            <div class="p-3 bg-light rounded-4 border">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold"
                                                        style="width: 40px; height: 40px;">
                                                        <?= mb_strtoupper(mb_substr($review['user_name'], 0, 1)) ?>
                                                    </div>
                                                    <div class="ms-3">
                                                        <div class="fw-bold text-dark"><?= e($review['user_name']) ?></div>
                                                        <div class="text-warning small">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i
                                                                    class="fas fa-star<?= $i <= $review['rating'] ? '' : '-o' ?>"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                    <div class="ms-auto text-muted small">
                                                        <?= Helper::timeAgo($review['created_at']) ?></div>
                                                </div>
                                                <p class="mb-0 text-secondary small italic">
                                                    "<?= nl2br(e($review['comment'])) ?>"</p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="far fa-comments fs-1 text-muted mb-3 d-block"></i>
                                    <h6 class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</h6>
                                    <p class="small text-muted mb-0">Hãy là người đầu tiên trải nghiệm và để lại nhận xét!
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div id="warranty" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3">Quy định chung:</h6>
                                    <ul class="small text-secondary list-unstyled">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Bảo hành 1 đổi 1
                                            nếu sản phẩm lỗi kỹ thuật.</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Thời gian xử lý
                                            khiếu nại trong vòng 24h.</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Hỗ trợ kỹ thuật
                                            trọn đời sau khi mua.</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3">Trường hợp từ chối bảo hành:</h6>
                                    <ul class="small text-secondary list-unstyled">
                                        <li class="mb-2"><i class="fas fa-times text-danger me-2"></i> Sản phẩm đã quá
                                            hạn bảo hành quy định.</li>
                                        <li class="mb-2"><i class="fas fa-times text-danger me-2"></i> Do lỗi của người
                                            dùng trong quá trình sử dụng.</li>
                                        <li class="mb-2"><i class="fas fa-times text-danger me-2"></i> Đã can thiệp hoặc
                                            thay đổi cấu trúc sản phẩm.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
            <div class="row mt-5">
                <div class="col-12 d-flex justify-content-between align-items-end mb-4">
                    <h3 class="fw-bold mb-0">Sản phẩm liên quan</h3>
                    <a href="<?= url('/category/' . $product['category_slug']) ?>"
                        class="text-decoration-none text-primary fw-bold">
                        Xem tất cả <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>
                <div class="row g-4">
                    <?php foreach ($relatedProducts as $rp): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all"
                                style="border-radius: 12px; overflow: hidden;">
                                <div class="position-relative">
                                    <img src="<?= asset($rp['thumbnail'] ?? 'images/no-image.png') ?>" class="card-img-top"
                                        alt="<?= e($rp['name']) ?>" style="height: 180px; object-fit: cover;">
                                    <a href="<?= url('/product/' . $rp['slug']) ?>" class="stretched-link"></a>
                                </div>
                                <div class="card-body p-3">
                                    <h6 class="card-title text-truncate mb-2">
                                        <a href="<?= url('/product/' . $rp['slug']) ?>"
                                            class="text-dark text-decoration-none hover-primary fw-bold">
                                            <?= e(Helper::truncate($rp['name'], 40)) ?>
                                        </a>
                                    </h6>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span
                                            class="text-danger fw-bold fs-5"><?= money($rp['sale_price'] ?? $rp['price']) ?></span>
                                        <span class="badge bg-light text-muted fw-normal" style="font-size: 0.7rem;">Đã bán:
                                            <?= $rp['total_sold'] ?? 0 ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

        <!-- Chat Modal -->
        <div class="modal fade" id="chatWithSellerModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
                <div class="modal-content border-0" style="border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
                    <!-- Header -->
                    <div class="px-4 py-3 d-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);border-bottom:none;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-white d-flex align-items-center justify-content-center fw-bold" id="chatSellerAvatar" style="width:38px;height:38px;color:#6366f1;font-size:1rem;flex-shrink:0;">S</div>
                            <div>
                                <div class="fw-semibold text-white" id="chatSellerName" style="font-size:0.95rem;line-height:1.2;">Người bán</div>
                                <div id="chatSellerStatus" class="d-flex align-items-center gap-1" style="font-size:0.7rem;opacity:0.85;color:#fff;">
                                    <span style="width:6px;height:6px;border-radius:50%;background:#4ade80;display:inline-block;"></span> Đang trực tuyến
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <!-- Messages -->
                    <div class="modal-body p-0" style="background:#f8fafc;">
                        <div id="chatMessageHistory" class="px-3 py-3" style="height:400px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;">
                            <div class="text-center text-muted py-5">
                                <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
                                <div class="small">Đang tải...</div>
                            </div>
                        </div>
                    </div>
                    <!-- Footer input -->
                    <div class="px-3 py-3 bg-white" style="border-top:1px solid #e9ecef;">
                        <div class="d-flex align-items-end gap-2 rounded-3 px-3 py-2" style="background:#f1f5f9;">
                            <textarea id="chatMessageInput" class="form-control border-0 bg-transparent flex-grow-1 p-0" placeholder="Nhập tin nhắn..." style="box-shadow:none;resize:none;min-height:22px;max-height:140px;font-size:0.875rem;line-height:1.5;" rows="1" oninput="this.style.height='';this.style.height=Math.min(this.scrollHeight,140)+'px'"></textarea>
                            <button id="sendChatMessageBtn" class="btn rounded-circle d-flex align-items-center justify-content-center p-0 mb-1" style="width:32px;height:32px;flex-shrink:0;background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;color:#fff;">
                                <i class="fas fa-paper-plane" style="font-size:0.75rem;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let chatSellerId = null, chatInterval = null;

            function timeAgo(d) {
                if (!d) return '';
                const s = Math.floor((new Date() - new Date(d.replace(' ','T'))) / 1000);
                if (s < 60) return 'vài giây trước';
                if (s < 3600) return Math.floor(s/60) + ' phút trước';
                if (s < 86400) return Math.floor(s/3600) + ' giờ trước';
                return Math.floor(s/86400) + ' ngày trước';
            }

            function renderMsgHtml(msg, uid) {
                const me = msg.sender_id == uid;
                const body = msg.message ? `<div style="font-size:0.875rem;white-space:pre-wrap;word-break:break-word;line-height:1.5;">${msg.message}</div>` : '';
                const t = new Date(msg.created_at.replace(' ','T')).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
                const s = me ? 'background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-radius:18px 18px 4px 18px;' : 'background:#fff;color:#1e293b;border-radius:18px 18px 18px 4px;box-shadow:0 1px 4px rgba(0,0,0,0.08);';
                return `<div class="d-flex ${me?'justify-content-end':'justify-content-start'}"><div style="max-width:75%;padding:8px 14px;${s}">${body}<div style="font-size:0.6rem;opacity:0.6;text-align:${me?'right':'left'};margin-top:2px;">${t}</div></div></div>`;
            }

            function openChatModal(sellerId, sellerName) {
                if (!<?= Auth::check() ? 'true' : 'false' ?>) {
                    Swal.fire({icon:'info',title:'Đăng nhập',text:'Vui lòng đăng nhập để nhắn tin',confirmButtonText:'Đăng nhập',showCancelButton:true})
                        .then(r => { if (r.isConfirmed) window.location.href='<?= url('/login') ?>'; });
                    return;
                }
                chatSellerId = sellerId;
                document.getElementById('chatSellerName').innerText = sellerName;
                document.getElementById('chatSellerAvatar').innerText = sellerName.charAt(0).toUpperCase();
                new bootstrap.Modal(document.getElementById('chatWithSellerModal')).show();
                loadChatMessages();
                if (chatInterval) clearInterval(chatInterval);
                chatInterval = setInterval(loadChatMessages, 3000);
            }

            async function loadChatMessages() {
                if (!chatSellerId) return;
                const el = document.getElementById('chatMessageHistory');
                try {
                    const r = await fetch(`<?= url('/api/chat/messages') ?>?seller_id=${chatSellerId}`);
                    if (!r.ok) throw new Error();
                    const d = await r.json();
                    if (!d.success) { el.innerHTML = `<div class="text-center text-danger small my-5">${d.message||'Lỗi'}</div>`; return; }
                    const bot = el.scrollHeight - el.scrollTop <= el.clientHeight + 60;
                    const st = document.getElementById('chatSellerStatus');
                    if (st) {
                        const dot = d.is_online ? '#4ade80' : '#9ca3af';
                        const txt = d.is_online ? 'Đang trực tuyến' : (d.last_active_at ? 'Hoạt động ' + timeAgo(d.last_active_at) : 'Ngoại tuyến');
                        st.innerHTML = `<span style="width:6px;height:6px;border-radius:50%;background:${dot};display:inline-block;"></span> ${txt}`;
                    }
                    el.innerHTML = d.messages.length === 0
                        ? '<div class="text-center text-muted small" style="margin-top:80px;"><i class="far fa-comment-dots" style="font-size:2rem;opacity:0.2;display:block;margin-bottom:8px;"></i>Hãy bắt đầu cuộc trò chuyện!</div>'
                        : d.messages.map(m => renderMsgHtml(m, d.current_user_id)).join('');
                    if (bot) el.scrollTop = el.scrollHeight;
                } catch(e) {
                    if (el.querySelector('.spinner-border')) el.innerHTML = '<div class="text-center text-danger small my-5">Lỗi kết nối</div>';
                }
            }

            async function sendChatMessage() {
                const inp = document.getElementById('chatMessageInput');
                const btn = document.getElementById('sendChatMessageBtn');
                if (!inp.value.trim()) return;
                const ob = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:0.7rem;"></i>';
                try {
                    const fd = new FormData();
                    fd.append('seller_id', chatSellerId);
                    fd.append('message', inp.value.trim());
                    fd.append('csrf_token', '<?= csrf_token() ?>');
                    const r = await fetch('<?= url('/api/chat/send') ?>', {method:'POST',body:fd});
                    const d = await r.json();
                    if (d.success) {
                        inp.value = ''; inp.style.height = '22px';
                        if (d.messages && d.current_user_id) {
                            const el = document.getElementById('chatMessageHistory');
                            el.innerHTML = d.messages.map(m => renderMsgHtml(m, d.current_user_id)).join('');
                            el.scrollTop = el.scrollHeight;
                        }
                    } else {
                        Swal.fire({icon:'error',title:'Lỗi',text:d.message||'Không thể gửi',toast:true,position:'top-end',showConfirmButton:false,timer:3000});
                    }
                } catch(e) {
                    Swal.fire({icon:'error',title:'Lỗi',text:'Lỗi kết nối',toast:true,position:'top-end',showConfirmButton:false,timer:3000});
                } finally {
                    btn.disabled = false; btn.innerHTML = ob;
                    document.getElementById('chatMessageInput').focus();
                }
            }

            document.getElementById('sendChatMessageBtn').addEventListener('click', sendChatMessage);
            document.getElementById('chatMessageInput').addEventListener('keydown', e => {
                if (e.key==='Enter' && !e.shiftKey) { e.preventDefault(); sendChatMessage(); }
            });
            document.getElementById('chatWithSellerModal').addEventListener('hidden.bs.modal', () => {
                if (chatInterval) clearInterval(chatInterval);
                chatInterval = null; chatSellerId = null;
            });
        </script>

        <style>
    .bg-success-soft {
        background-color: rgba(25, 135, 84, 0.1);
    }

    .line-height-lg {
        line-height: 1.8;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
    }

    .nav-pills .nav-link {
        color: #6c757d;
        border-radius: 0;
        position: relative;
    }

    .nav-pills .nav-link.active {
        background-color: transparent !important;
        color: #0d6efd !important;
    }

    .nav-pills .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 20%;
        right: 20%;
        height: 3px;
        background-color: #0d6efd;
        border-radius: 3px 3px 0 0;
    }

    .main-product-image {
        transition: transform 0.5s ease;
    }

    .main-product-image:hover {
        transform: scale(1.05);
    }

    .breadcrumb-item+.breadcrumb-item::before {
        content: "\f105";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        font-size: 0.7rem;
        color: #adb5bd;
    }

    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>



