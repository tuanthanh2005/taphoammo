<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Quản lý danh mục</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="fas fa-plus"></i> Thêm danh mục</button>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Icon</th>
                            <th>Tên danh mục</th>
                            <th>Đường dẫn (Slug)</th>
                            <th>Sắp xếp</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">Không có dữ liệu</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>#<?= $category['id'] ?></td>
                                    <td>
                                        <i class="<?= $category['icon'] ?? 'fas fa-folder' ?> fa-2x text-secondary"></i>
                                    </td>
                                    <td>
                                        <span class="fw-bold"><?= e($category['name']) ?></span>
                                    </td>
                                    <td><code><?= e($category['slug']) ?></code></td>
                                    <td><?= $category['display_order'] ?></td>
                                    <td>
                                        <?php if ($category['status'] === 'active'): ?>
                                            <span class="badge bg-success">Hiển thị</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Đang ẩn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?= $category['id'] ?>" title="Sửa"><i class="fas fa-edit"></i></button>
                                        <form action="<?= url('/admin/categories/delete/' . $category['id']) ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa danh mục này? Sản phẩm bên trong sẽ bị ảnh hưởng!')"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editCategoryModal<?= $category['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="<?= url('/admin/categories/update/' . $category['id']) ?>" method="POST">
                                                <?= csrf_field() ?>
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Sửa danh mục</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Tên danh mục</label>
                                                        <input type="text" name="name" class="form-control" value="<?= e($category['name']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Đường dẫn (Slug - để trống tự tạo)</label>
                                                        <input type="text" name="slug" class="form-control" value="<?= e($category['slug']) ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">FontAwesome Icon Class</label>
                                                        <input type="text" name="icon" class="form-control" value="<?= e($category['icon']) ?>">
                                                        <small class="text-muted">Ví dụ: fas fa-laptop</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Thứ tự sắp xếp</label>
                                                        <input type="number" name="display_order" class="form-control" value="<?= $category['display_order'] ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Trạng thái</label>
                                                        <select name="status" class="form-select">
                                                            <option value="active" <?= $category['status'] === 'active' ? 'selected' : '' ?>>Hiển thị</option>
                                                            <option value="inactive" <?= $category['status'] === 'inactive' ? 'selected' : '' ?>>Đang ẩn</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/admin/categories/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Thêm danh mục mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Đường dẫn (Slug - để trống tự tạo)</label>
                        <input type="text" name="slug" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">FontAwesome Icon Class</label>
                        <input type="text" name="icon" class="form-control" value="fas fa-folder">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Thứ tự sắp xếp</label>
                        <input type="number" name="display_order" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="active">Hiển thị</option>
                            <option value="inactive">Đang ẩn</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Thêm danh mục</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý danh mục';
require_once __DIR__ . '/../layouts/admin.php';
?>
