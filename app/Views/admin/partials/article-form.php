<?php $articleData = $article ?? []; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="mb-3">
            <label class="form-label fw-semibold">Tiêu đề</label>
            <input type="text" name="title" class="form-control form-control-lg article-title-input" value="<?= e(old('title', $articleData['title'] ?? '')) ?>" required placeholder="Nhập tiêu đề bài viết...">
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Đường dẫn (Slug)</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted border-end-0">/bai-viet/</span>
                <input type="text" name="slug_display" class="form-control border-start-0 ps-0 text-muted article-slug-input" value="<?= e($articleData['slug'] ?? '') ?>" readonly tabindex="-1">
            </div>
            <div class="form-text small text-muted mt-1">Đường dẫn tự động tạo theo tiêu đề để tối ưu SEO.</div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Mô tả ngắn</label>
            <textarea name="excerpt" class="form-control" rows="2" placeholder="1-2 câu tóm tắt bài viết để hiển thị ở danh sách bài viết..."><?= e(old('excerpt', $articleData['excerpt'] ?? '')) ?></textarea>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label fw-semibold mb-0">Nội dung</label>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary px-3" data-insert="# " title="Tiêu đề chính">H2</button>
                    <button type="button" class="btn btn-outline-secondary px-3" data-insert="## " title="Tiêu đề phụ">H3</button>
                    <button type="button" class="btn btn-outline-secondary px-3" data-insert="- " title="Danh sách">List</button>
                    <button type="button" class="btn btn-outline-primary px-3 btn-upload-article-image" title="Upload & Chèn ảnh">
                        <i class="fas fa-image"></i>
                    </button>
                </div>
            </div>
            <textarea name="content" class="form-control article-content-input" rows="12" required placeholder="Nội dung bài viết..."><?= e(old('content', $articleData['content'] ?? '')) ?></textarea>
            <div class="form-text d-flex justify-content-between mt-1">
                <span>Dùng Markdown đơn giản: # H2, ## H3, - List, **Đậm**, *Nghiêng*.</span>
                <span class="text-primary cursor-pointer btn-preview-article" style="cursor:pointer;"><i class="fas fa-eye"></i> Xem trước nhanh</span>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border shadow-none rounded-4 mb-3">
            <div class="card-body">
                <label class="form-label fw-semibold">Ảnh bìa</label>
                <input type="file" name="cover_image" class="form-control mb-2" accept="image/*">
                <?php if (!empty($articleData['cover_image'])): ?>
                    <div class="rounded-3 overflow-hidden border bg-light mb-2" style="aspect-ratio:16/9;">
                        <img src="<?= asset($articleData['cover_image']) ?>" alt="Cover" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                <?php endif; ?>
                <div class="form-text small">Ảnh hiển thị ở trang chủ và đầu bài viết.</div>
            </div>
        </div>

        <div class="card border shadow-none rounded-4 mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="draft" <?= (old('status', $articleData['status'] ?? 'draft')) === 'draft' ? 'selected' : '' ?>>Nháp (Chưa hiện)</option>
                        <option value="published" <?= (old('status', $articleData['status'] ?? '')) === 'published' ? 'selected' : '' ?>>Công khai (Hiện ngay)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card border shadow-none rounded-4">
            <div class="card-header bg-transparent fw-semibold py-3 border-bottom-0">SEO Nâng cao (Tùy chọn)</div>
            <div class="card-body pt-0">
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">SEO Title</label>
                    <input type="text" name="seo_title" class="form-control form-control-sm" value="<?= e(old('seo_title', $articleData['seo_title'] ?? '')) ?>" placeholder="Mặc định dùng tiêu đề bài viết">
                </div>

                <div class="mb-0">
                    <label class="form-label small text-muted mb-1">SEO Description</label>
                    <textarea name="seo_description" class="form-control form-control-sm" rows="3" placeholder="Mặc định dùng mô tả ngắn"><?= e(old('seo_description', $articleData['seo_description'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>

        <div class="mt-4 rounded-4 p-3 border bg-light small text-secondary">
            <div class="fw-semibold text-dark mb-2"><i class="fas fa-lightbulb text-warning"></i> Mẹo hay</div>
            <ul class="ps-3 mb-0">
                <li class="mb-1">Tách nhỏ bài viết bằng các tiêu đề <strong>H2, H3</strong>.</li>
                <li class="mb-1">Nên có ít nhất 1 ảnh bìa đẹp để hút view.</li>
                <li>Mô tả ngắn nên chứa từ khóa chính của bài viết.</li>
            </ul>
        </div>
    </div>
</div>

<script>
(function() {
    // We use a self-invoking function to avoid global scope pollution
    // but the script will be included multiple times if we don't check for existence
    if (window.articleFormInited) return;
    window.articleFormInited = true;

    // Helper to slugify
    function slugify(text) {
        return text.toString().toLowerCase()
            .replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a')
            .replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e')
            .replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i')
            .replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o')
            .replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u')
            .replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y')
            .replace(/đ/gi, 'd')
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');            // Trim - from end of text
    }

    document.addEventListener('input', function(e) {
        if (e.target.matches('.article-title-input')) {
            const modalBody = e.target.closest('.modal-body');
            const slugInput = modalBody ? modalBody.querySelector('.article-slug-input') : null;
            if (slugInput) {
                slugInput.value = slugify(e.target.value);
            }
        }
    });

    document.addEventListener('click', function(e) {
        // Toolbar buttons and other clicks handled in admin/articles.php
    });
})();
</script>
