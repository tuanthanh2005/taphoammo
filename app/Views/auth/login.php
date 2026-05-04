<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main class="login-page">
    <div class="container">
        <div class="login-shell">
            <section class="login-card">
                <div class="login-brand">
                    <div class="login-logo">AI</div>
                    <div>
                        <h1>Đăng nhập</h1>
                        <p>Truy cập tài khoản AI CỦA TÔI của bạn</p>
                    </div>
                </div>

                <form action="<?= url('/login') ?>" method="POST" class="login-form">
                    <?= csrf_field() ?>

                    <div class="form-field">
                        <label for="login-email">Email hoặc username</label>
                        <div class="input-shell">
                            <i class="far fa-envelope"></i>
                            <input
                                id="login-email"
                                type="text"
                                name="email"
                                value="<?= e(old('email')) ?>"
                                placeholder="email@example.com"
                                autocomplete="username"
                                required>
                        </div>
                    </div>

                    <div class="form-field">
                        <div class="label-row">
                            <label for="login-password">Mật khẩu</label>
                            <a href="#" class="muted-link">Quên mật khẩu?</a>
                        </div>
                        <div class="input-shell">
                            <i class="fas fa-lock"></i>
                            <input
                                id="login-password"
                                type="password"
                                name="password"
                                placeholder="Nhập mật khẩu"
                                autocomplete="current-password"
                                required>
                        </div>
                    </div>

                    <button type="submit" class="login-submit">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                        Đăng nhập
                    </button>
                </form>

                <div class="login-divider">
                    <span>Hoặc</span>
                </div>

                <a href="<?= url('/auth/google') ?>" class="google-login">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18" height="18" alt="">
                    Tiếp tục với Google
                </a>

                <p class="login-register">
                    Chưa có tài khoản?
                    <a href="<?= url('/register') ?>">Đăng ký ngay</a>
                </p>
            </section>

            <aside class="login-notes">
                <div class="notes-icon">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h2>Bảo mật tài khoản</h2>
                <p>Chỉ đăng nhập trên tên miền chính thức và không chia sẻ mật khẩu, OTP hoặc thông tin vi phạm cho bất kỳ ai.</p>

                <div class="note-list">
                    <div class="note-item">
                        <i class="fas fa-check"></i>
                        <span>Giao dịch qua hệ thống để được bảo vệ.</span>
                    </div>
                    <div class="note-item">
                        <i class="fas fa-check"></i>
                        <span>Kiểm tra kỹ thông tin sản phẩm trước khi mua.</span>
                    </div>
                    <div class="note-item">
                        <i class="fas fa-check"></i>
                        <span>Cần hổ trợ, liên hệ kênh Telegram chính thức.</span>
                    </div>
                </div>

                <a href="https://t.me/specademy" target="_blank" rel="noopener" class="support-link">
                    <i class="fab fa-telegram"></i>
                    @specademy
                </a>
            </aside>
        </div>
    </div>
</main>

<style>
.login-page {
    min-height: calc(100vh - 170px);
    display: flex;
    align-items: center;
    padding: 56px 0;
    background:
        radial-gradient(circle at top left, rgba(139, 92, 246, 0.14), transparent 34%),
        linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
}

.login-shell {
    max-width: 980px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 24px;
    align-items: stretch;
}

.login-card,
.login-notes {
    border: 1px solid #e6eaf2;
    border-radius: 8px;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
}

.login-card {
    background: #fff;
    padding: 36px;
}

.login-brand {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 28px;
}

.login-logo {
    width: 56px;
    height: 56px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #7c3aed, #22c55e);
    color: #fff;
    font-weight: 800;
    font-size: 1.15rem;
    flex: 0 0 auto;
}

.login-brand h1 {
    margin: 0 0 4px;
    font-size: 1.75rem;
    font-weight: 800;
    color: #111827;
}

.login-brand p {
    margin: 0;
    color: #64748b;
    font-size: 0.95rem;
}

.login-form {
    display: grid;
    gap: 18px;
}

.form-field label {
    display: block;
    margin-bottom: 8px;
    color: #334155;
    font-weight: 700;
    font-size: 0.88rem;
}

.label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.muted-link {
    color: #7c3aed;
    font-size: 0.85rem;
    text-decoration: none;
    font-weight: 600;
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

.login-submit,
.google-login,
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

.login-submit {
    margin-top: 4px;
    border: 0;
    background: #7c3aed;
    color: #fff;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.login-submit:hover {
    background: #6d28d9;
    transform: translateY(-1px);
    box-shadow: 0 12px 28px rgba(124, 58, 237, 0.26);
}

.login-divider {
    position: relative;
    margin: 26px 0;
    text-align: center;
    color: #94a3b8;
    font-size: 0.86rem;
}

.login-divider::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e5e7eb;
}

.login-divider span {
    position: relative;
    background: #fff;
    padding: 0 14px;
}

.google-login {
    width: 100%;
    border: 1px solid #dbe2ea;
    color: #334155;
    background: #fff;
}

.google-login:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    color: #111827;
}

.login-register {
    margin: 22px 0 0;
    text-align: center;
    color: #64748b;
    font-size: 0.92rem;
}

.login-register a {
    color: #7c3aed;
    font-weight: 800;
    text-decoration: none;
}

.login-notes {
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

.login-notes h2 {
    font-size: 1.35rem;
    font-weight: 800;
    margin: 0 0 10px;
}

.login-notes p {
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
    .login-shell {
        grid-template-columns: 1fr;
        max-width: 560px;
    }

    .login-notes {
        display: none;
    }
}

@media (max-width: 575.98px) {
    .login-page {
        padding: 28px 0;
    }

    .login-card {
        padding: 24px;
    }

    .login-brand {
        align-items: flex-start;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
