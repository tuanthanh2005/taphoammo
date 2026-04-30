<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Quản lý Menu Header</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMenuModal"><i class="fas fa-plus"></i> Thêm menu</button>
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
                            <th>Tên menu</th>
                            <th>Đường dẫn (URL)</th>
                            <th>Menu cha</th>
                            <th>Sắp xếp</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($menus)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">Không có dữ liệu</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($menus as $menu): ?>
                                <tr>
                                    <td>#<?= $menu['id'] ?></td>
                                    <td>
                                        <?php if ($menu['icon']): ?>
                                            <i class="<?= e($menu['icon']) ?> fa-2x text-secondary"></i>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold <?= $menu['parent_id'] ? 'ms-3 text-muted' : '' ?>">
                                            <?= $menu['parent_id'] ? '— ' : '' ?><?= e($menu['title']) ?>
                                        </span>
                                    </td>
                                    <td><code><?= e($menu['url']) ?></code></td>
                                    <td>
                                        <?= $menu['parent_id'] ? '<span class="badge bg-info text-dark">'.e($menu['parent_name']).'</span>' : '<span class="badge bg-secondary">Gốc</span>' ?>
                                    </td>
                                    <td><?= $menu['display_order'] ?></td>
                                    <td>
                                        <?php if ($menu['status'] === 'active'): ?>
                                            <span class="badge bg-success">Hiển thị</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Đang ẩn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMenuModal<?= $menu['id'] ?>" title="Sửa"><i class="fas fa-edit"></i></button>
                                        <form action="<?= url('/admin/menus/delete/' . $menu['id']) ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa menu này? Các menu con của nó cũng sẽ bị xóa!')"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editMenuModal<?= $menu['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="<?= url('/admin/menus/update/' . $menu['id']) ?>" method="POST">
                                                <?= csrf_field() ?>
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Sửa menu</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Tên menu <span class="text-danger">*</span></label>
                                                        <input type="text" name="title" class="form-control" value="<?= e($menu['title']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Đường dẫn (URL)</label>
                                                        <input type="text" name="url" class="form-control" value="<?= e($menu['url']) ?>" placeholder="/products hoặc https://...">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">FontAwesome Icon Class</label>
                                                        <input type="text" name="icon" class="form-control" value="<?= e($menu['icon']) ?>">
                                                        <small class="text-muted">Ví dụ: fas fa-laptop</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Menu cha</label>
                                                        <select name="parent_id" class="form-select">
                                                            <option value="">-- Là menu gốc --</option>
                                                            <?php foreach ($parents as $p): ?>
                                                                <?php if ($p['id'] != $menu['id']): ?>
                                                                <option value="<?= $p['id'] ?>" <?= $p['id'] == $menu['parent_id'] ? 'selected' : '' ?>><?= e($p['title']) ?></option>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Thứ tự sắp xếp</label>
                                                            <input type="number" name="display_order" class="form-control" value="<?= $menu['display_order'] ?>">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Trạng thái</label>
                                                            <select name="status" class="form-select">
                                                                <option value="active" <?= $menu['status'] === 'active' ? 'selected' : '' ?>>Hiển thị</option>
                                                                <option value="inactive" <?= $menu['status'] === 'inactive' ? 'selected' : '' ?>>Đang ẩn</option>
                                                            </select>
                                                        </div>
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
<div class="modal fade" id="addMenuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/admin/menus/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Thêm menu mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên menu <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Đường dẫn (URL)</label>
                        <input type="text" name="url" class="form-control" value="#" placeholder="/products hoặc https://...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">FontAwesome Icon Class</label>
                        <input type="text" name="icon" class="form-control">
                        <small class="text-muted">Ví dụ: fas fa-laptop</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Menu cha</label>
                        <select name="parent_id" class="form-select">
                            <option value="">-- Là menu gốc --</option>
                            <?php foreach ($parents as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= e($p['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Thứ tự sắp xếp</label>
                            <input type="number" name="display_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Hiển thị</option>
                                <option value="inactive">Đang ẩn</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý Menu';
require_once __DIR__ . '/../layouts/admin.php';
?>
