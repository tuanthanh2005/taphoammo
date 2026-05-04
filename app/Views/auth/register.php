<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main class="register-page">
    <div class="container">
        <div class="register-shell">
            <section class="register-card">
                <div class="register-brand">
                    <div class="register-logo">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h1>Tạo tài khoản</h1>
                        <p>Đăng ký để mua bán sản phẩm số trên AI CỦA TÔI</p>
                    </div>
                </div>

                <form action="<?= url('/register') ?>" method="POST" class="register-form">
                    <?= csrf_field() ?>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="register-name">Họ tên</label>
                            <div class="input-shell">
                                <i class="far fa-user"></i>
                                <input
                                    id="register-name"
                                    type="text"
                                    name="name"
                                    value="<?= e(old('name')) ?>"
                                    placeholder="Tran Van A"
                                    autocomplete="name"
                                    required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="register-username">Username</label>
                            <div class="input-shell">
                                <i class="fas fa-at"></i>
                                <input
                                    id="register-username"
                                    type="text"
                                    name="username"
                                    value="<?= e(old('username')) ?>"
                                    placeholder="username"
                                    autocomplete="username"
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="register-email">Email</label>
                        <div class="input-shell">
                            <i class="far fa-envelope"></i>
                            <input
                                id="register-email"
                                type="email"
                                name="email"
                                value="<?= e(old('email')) ?>"
                                placeholder="email@example.com"
                                autocomplete="email"
                                required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="register-password">Mật khẩu</label>
                            <div class="input-shell">
                                <i class="fas fa-lock"></i>
                                <input
                                    id="register-password"
                                    type="password"
                                    name="password"
                                    placeholder="Ít nhất 6 ký tự"
                                    autocomplete="new-password"
                                    minlength="6"
                                    required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="register-confirm-password">Xác nhận mật khẩu</label>
                            <div class="input-shell">
                                <i class="fas fa-check-double"></i>
                                <input
                                    id="register-confirm-password"
                                    type="password"
                                    name="confirm_password"
                                    placeholder="Nhập lại mật khẩu"
                                    autocomplete="new-password"
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="register-referral">Mã giới thiệu</label>
                        <div class="input-shell">
                            <i class="fas fa-gift"></i>
                            <input
                                id="register-referral"
                                type="text"
                                name="referral_code"
                                value="<?= e($_GET['ref'] ?? '') ?>"
                                placeholder="Nhập mã giới thiệu nếu có">
                        </div>
                    </div>

                    <button type="submit" class="register-submit">
                        <i class="fas fa-paper-plane"></i>
                        Đăng ký
                    </button>
                </form>

                <div class="register-divider">
                    <span>Hoặc</span>
                </div>

                <a href="<?= url('/auth/google') ?>" class="google-register">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18" height="18" alt="">
                    Đăng ký với Google
                </a>

                <p class="register-login">
                    Đã có tài khoản?
                    <a href="<?= url('/login') ?>">Đăng nhập ngay</a>
                </p>
            </section>

            <aside class="register-notes">
                <div class="notes-icon">
                    <i class="fas fa-circle-info"></i>
                </div>
                <h2>ưu ý đăng ký</h2>
                <p>Vui lòng sử dụng thông tin chính xác để khai báo, nhận thông báo đơn hàng và bảo vệ tài khoản khi cần hỗ trợ.</p>

                <div class="note-list">
                    <div class="note-item">
                        <i class="fas fa-check"></i>
                        <span>Email nên là email thật để khôi phục tài khoản.</span>
                    </div>
                    <div class="note-item">
                        <i class="fas fa-check"></i>
                        <span>Username viết liền không dấu, dùng để đăng nhập.</span>
                    </div>
                    <div class="note-item">
                        <i class="fas fa-check"></i>
                        <span>Mật khẩu tối thiểu 6 ký tự và không chia sẻ cho bất kỳ ai.</span>
                    </div>
                </div>

                <a href="https://t.me/specademy" target="_blank" rel="noopener" class="support-link">
                    <i class="fab fa-telegram"></i>
                    Cần hỗ trợ? @specademy
                </a>
            </aside>
        </div>
    </div>
</main>

<style>
.register-page {
    min-height: calc(100vh - 170px);
    display: flex;
    align-items: center;
    padding: 56px 0;
    background:
        radial-gradient(circle at top right, rgba(34, 197, 94, 0.12), transparent 34%),
        radial-gradient(circle at top left, rgba(139, 92, 246, 0.14), transparent 34%),
        linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
}

.register-shell {
    max-width: 1040px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 24px;
    align-items: stretch;
}

.register-card,
.register-notes {
    border: 1px solid #e6eaf2;
    border-radius: 8px;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
}

.register-card {
    background: #fff;
    padding: 34px;
}

.register-brand {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 26px;
}

.register-logo {
    width: 56px;
    height: 56px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #7c3aed, #22c55e);
    color: #fff;
    font-size: 1.15rem;
    flex: 0 0 auto;
}

.register-brand h1 {
    margin: 0 0 4px;
    font-size: 1.75rem;
    font-weight: 800;
    color: #111827;
}

.register-brand p {
    margin: 0;
    color: #64748b;
    font-size: 0.95rem;
}

.register-form {
    display: grid;
    gap: 16px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.form-field label {
    display: block;
    margin-bottom: 8px;
    color: #334155;
    font-weight: 700;
    font-size: 0.88rem;
}

.input-shell {
    height: 50px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 14px;
    border: 1px solid #dbe2ea;
    border-radius: 8px;
    background: #f8fafc;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.input-shell:focus-within {
    background: #fff;
    border-color: #8b5cf6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.12);
}

.input-shell i {
    color: #94a3b8;
    width: 18px;
    text-align: center;
}

.input-shell input {
    width: 100%;
    min-width: 0;
    border: 0;
    outline: 0;
    background: transparent;
    color: #111827;
    font-size: 0.98rem;
}

.input-shell input::placeholder {
    color: #9ca3af;
}

.register-submit,
.google-register,
.support-link {
    height: 50px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-weight: 800;
    text-decoration: none;
}

.register-submit {
    margin-top: 4px;
    border: 0;
    background: #7c3aed;
    color: #fff;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.register-submit:hover {
    background: #6d28d9;
    transform: translateY(-1px);
    box-shadow: 0 12px 28px rgba(124, 58, 237, 0.26);
}

.register-divider {
    position: relative;
    margin: 24px 0;
    text-align: center;
    color: #94a3b8;
    font-size: 0.86rem;
}

.register-divider::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e5e7eb;
}

.register-divider span {
    position: relative;
    background: #fff;
    padding: 0 14px;
}

.google-register {
    width: 100%;
    border: 1px solid #dbe2ea;
    color: #334155;
    background: #fff;
}

.google-register:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    color: #111827;
}

.register-login {
    margin: 20px 0 0;
    text-align: center;
    color: #64748b;
    font-size: 0.92rem;
}

.register-login a {
    color: #7c3aed;
    font-weight: 800;
    text-decoration: none;
}

.register-notes {
    background: #111827;
    color: #fff;
    padding: 32px;
    display: flex;
    flex-direction: column;
}

.notes-icon {
    width: 52px;
    height: 52px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    color: #facc15;
    font-size: 1.3rem;
    margin-bottom: 20px;
}

.register-notes h2 {
    font-size: 1.35rem;
    font-weight: 800;
    margin: 0 0 10px;
}

.register-notes p {
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 22px;
    line-height: 1.6;
}

.note-list {
    display: grid;
    gap: 14px;
    margin-bottom: 24px;
}

.note-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    color: rgba(255, 255, 255, 0.78);
    font-size: 0.92rem;
    line-height: 1.45;
}

.note-item i {
    color: #22c55e;
    margin-top: 3px;
}

.support-link {
    margin-top: auto;
    width: 100%;
    background: rgba(14, 165, 233, 0.15);
    color: #7dd3fc;
    border: 1px solid rgba(125, 211, 252, 0.22);
}

.support-link:hover {
    color: #fff;
    background: rgba(14, 165, 233, 0.26);
}

@media (max-width: 991.98px) {
    .register-shell {
        grid-template-columns: 1fr;
        max-width: 620px;
    }

    .register-notes {
        display: none;
    }
}

@media (max-width: 575.98px) {
    .register-page {
        padding: 28px 0;
    }

    .register-card {
        padding: 24px;
    }

    .register-brand {
        align-items: flex-start;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
