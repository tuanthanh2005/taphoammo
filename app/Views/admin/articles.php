<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1 fw-bold">Quản lý bài viết</h2>
                <p class="text-muted mb-0">Quản lý nội dung, tin tức và hướng dẫn cho người dùng.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createArticleModal">
                <i class="fas fa-plus me-2"></i> Thêm bài viết
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3">ID</th>
                            <th>Ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Trạng thái</th>
                            <th>Ngày đăng</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php if (empty($articles)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="py-4">
                                        <i class="fas fa-newspaper fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-0">Chưa có bài viết nào được tạo.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($articles as $article): ?>
                                <tr>
                                    <td class="ps-4 text-muted small">#<?= $article['id'] ?></td>
                                    <td>
                                        <div style="width:72px;height:40px;border-radius:8px;overflow:hidden;background:#eef2f7;">
                                            <img src="<?= !empty($article['cover_image']) ? asset($article['cover_image']) : asset('images/no-image.png') ?>" alt="<?= e($article['title']) ?>" style="width:100%;height:100%;object-fit:cover;">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= e($article['title']) ?></div>
                                        <div class="small text-muted">/bai-viet/<?= e($article['slug']) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($article['status'] === 'published'): ?>
                                            <span class="badge bg-success-subtle text-success px-3">Công khai</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary px-3">Nháp</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small"><?= !empty($article['published_at']) ? Helper::formatDate($article['published_at']) : '<span class="text-muted">Chưa đăng</span>' ?></div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-white border shadow-none" data-bs-toggle="modal" data-bs-target="#editArticleModal<?= $article['id'] ?>" title="Sửa">
                                                <i class="fas fa-edit text-primary"></i>
                                            </button>
                                            <?php if ($article['status'] === 'published'): ?>
                                                <a href="<?= url('/bai-viet/' . $article['slug']) ?>" target="_blank" class="btn btn-white border shadow-none" title="Xem">
                                                    <i class="fas fa-eye text-dark"></i>
                                                </a>
                                            <?php endif; ?>
                                            <form action="<?= url('/admin/bai-viet/delete/' . $article['id']) ?>" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-white border shadow-none" onclick="return confirm('Xác nhận xóa bài viết này? Hành động này không thể hoàn tác.')" title="Xóa">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (($totalPages ?? 1) > 1): ?>
            <div class="card-footer bg-white border-top py-3">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link px-3" href="<?= url('/admin/bai-viet?page=' . $i) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modals outside the loop for cleaner HTML -->
<?php foreach ($articles as $article): ?>
    <div class="modal fade" id="editArticleModal<?= $article['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form action="<?= url('/admin/bai-viet/update/' . $article['id']) ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Chỉnh sửa bài viết</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php include __DIR__ . '/partials/article-form.php'; ?>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<div class="modal fade" id="createArticleModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('/admin/bai-viet/store') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Thêm bài viết mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php $article = null; include __DIR__ . '/partials/article-form.php'; ?>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Tạo bài viết</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="articlePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Xem trước bài viết</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 p-lg-5">
                <div id="previewContent" class="article-content">
                    <!-- Content will be injected here -->
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng xem trước</button>
            </div>
        </div>
    </div>
</div>

<input type="file" id="globalArticleImageUpload" style="display: none;" accept="image/*">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('globalArticleImageUpload');
    let currentTextarea = null;

    // Handle Image Upload from Toolbars
    document.addEventListener('click', function(e) {
        // Toolbar buttons
        if (e.target.matches('[data-insert]')) {
            const insertText = e.target.dataset.insert;
            const modalBody = e.target.closest('.modal-body');
            const textarea = modalBody ? modalBody.querySelector('.article-content-input') : null;
            
            if (textarea) {
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const current = textarea.value;
                const prefix = current && start > 0 && current[start-1] !== "\n" ? "\n" : "";
                
                textarea.value = current.slice(0, start) + prefix + insertText + current.slice(end);
                textarea.focus();
                textarea.setSelectionRange(start + prefix.length + insertText.length, start + prefix.length + insertText.length);
            }
        }

        const btn = e.target.closest('.btn-upload-article-image');
        if (btn) {
            const modalBody = btn.closest('.modal-body');
            currentTextarea = modalBody ? modalBody.querySelector('.article-content-input') : null;
            if (currentTextarea) {
                fileInput.click();
            }
        }

        // Preview button logic
        const previewBtn = e.target.closest('.btn-preview-article');
        if (previewBtn) {
            const modalBody = previewBtn.closest('.modal-body');
            const content = modalBody.querySelector('.article-content-input').value;
            const title = modalBody.querySelector('.article-title-input').value || 'Tiêu đề bài viết';
            
            let html = `<h1 class="display-6 fw-bold mb-4">${title}</h1>`;
            
            // Simple JS implementation of Helper::renderArticleContent
            const blocks = content.trim().split(/\n\s*\n+/);
            blocks.forEach(block => {
                block = block.trim();
                if (!block) return;

                // Images
                let imgMatch = block.match(/^\[img\](https?:\/\/[^\s\[\]]+)\[\/img\]$/i) || block.match(/^(https?:\/\/\S+\.(?:jpg|jpeg|png|gif|webp|svg))(?:\?\S*)?$/i);
                if (imgMatch) {
                    html += `<figure class="article-media my-4"><div class="article-media-frame" style="border-radius:16px;overflow:hidden;background:#f8fafc;border:1px solid #eee;padding:10px;display:flex;justify-content:center;"><img src="${imgMatch[1]}" style="max-width:100%;height:auto;border-radius:8px;"></div></figure>`;
                    return;
                }

                // Headers
                if (block.startsWith('## ')) {
                    html += `<h3 class="fw-bold mt-4 mb-3">${block.substring(3)}</h3>`;
                    return;
                }
                if (block.startsWith('# ')) {
                    html += `<h2 class="fw-bold mt-4 mb-3">${block.substring(2)}</h2>`;
                    return;
                }

                // Lists
                const lines = block.split('\n');
                if (lines.every(l => l.trim().startsWith('- ') || l.trim().startsWith('* '))) {
                    html += '<ul class="mb-3">';
                    lines.forEach(l => {
                        html += `<li>${l.trim().substring(2)}</li>`;
                    });
                    html += '</ul>';
                    return;
                }

                // Paragraphs
                let pContent = block.replace(/\n/g, '<br>');
                // Basic bold/italic
                pContent = pContent.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
                pContent = pContent.replace(/\*(.+?)\*/g, '<em>$1</em>');
                
                html += `<p class="mb-3" style="line-height:1.8; color:#444;">${pContent}</p>`;
            });

            document.getElementById('previewContent').innerHTML = html;
            const previewModal = new bootstrap.Modal(document.getElementById('articlePreviewModal'));
            previewModal.show();
        }
    });

    fileInput.addEventListener('change', function() {
        if (!fileInput.files || !fileInput.files[0] || !currentTextarea) return;

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('csrf_token', '<?= csrf_token() ?>');

        // Show loading state on the button that triggered the upload
        const btns = document.querySelectorAll('.btn-upload-article-image');
        btns.forEach(b => b.disabled = true);

        fetch('<?= url("/admin/bai-viet/upload-image") ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const insertText = `[img]${data.url}[/img]`;
                const start = currentTextarea.selectionStart;
                const end = currentTextarea.selectionEnd;
                const current = currentTextarea.value;
                const prefix = current && start > 0 && current[start-1] !== "\n" ? "\n\n" : "";
                const suffix = current && end < current.length && current[end] !== "\n" ? "\n\n" : "";
                
                currentTextarea.value = current.slice(0, start) + prefix + insertText + suffix + current.slice(end);
                currentTextarea.focus();
                
                // Trigger change event
                currentTextarea.dispatchEvent(new Event('input', { bubbles: true }));
            } else {
                alert(data.message || 'Lỗi khi upload ảnh');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Lỗi hệ thống khi upload ảnh');
        })
        .finally(() => {
            btns.forEach(b => b.disabled = false);
            fileInput.value = '';
        });
    });

    // Auto open modal if validation errors exist (check for session flashes)
    // This part is handled by PHP old() values normally, but we can help UX here.
    <?php if (Session::hasFlash('error') && !empty($_POST)): ?>
        const createModal = new bootstrap.Modal(document.getElementById('createArticleModal'));
        createModal.show();
    <?php endif; ?>
});
</script>

<style>
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
.bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1); }
.btn-white { background: #fff; color: #444; }
.btn-white:hover { background: #f8fafc; color: #000; }
.cursor-pointer { cursor: pointer; }
.modal { overflow-y: auto !important; }
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý bài viết';
require_once __DIR__ . '/../layouts/admin.php';
?>
