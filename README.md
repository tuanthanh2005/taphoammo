# MMO Marketplace - Website Thương Mại Điện Tử Sản Phẩm Số

Website marketplace bán sản phẩm số được xây dựng bằng PHP thuần theo mô hình MVC, dễ deploy lên hosting phổ thông.

## Tính năng chính

### Phân quyền người dùng
- **Admin**: Quản lý toàn bộ hệ thống
- **Seller**: Đăng bán sản phẩm, quản lý kho, rút tiền
- **User**: Mua sản phẩm, xem lịch sử đơn hàng
- **Affiliate**: Giới thiệu và nhận hoa hồng

### Chức năng nổi bật
- ✅ Đăng nhập/Đăng ký thông thường
- ✅ Đăng nhập bằng Google OAuth
- ✅ Quản lý sản phẩm số (key, account, file, link)
- ✅ Giỏ hàng và thanh toán
- ✅ Ví nội bộ
- ✅ Hệ thống rút tiền cho Seller (phí 5%)
- ✅ Phí admin trên mỗi đơn hàng (5%)
- ✅ Tự động giao hàng sau thanh toán
- ✅ Đánh giá sản phẩm
- ✅ Hệ thống affiliate

## Yêu cầu hệ thống

- PHP 8.1 hoặc cao hơn
- MySQL 5.7 hoặc MariaDB 10.3+
- Apache/Nginx với mod_rewrite
- Extension: PDO, PDO_MySQL, mbstring, openssl, curl

## Cài đặt

### 1. Upload source code

Upload toàn bộ source code lên hosting của bạn.

### 2. Tạo database

- Tạo database mới trong cPanel/Hosting Panel
- Import file `database.sql` vào database vừa tạo

### 3. Cấu hình

Sao chép file `.env.example` thành `.env` và cập nhật thông tin:

```env
APP_NAME="MMO Marketplace"
APP_URL="https://yourdomain.com"
APP_ENV=production
APP_DEBUG=false

DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password

GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback

ADMIN_ORDER_FEE_PERCENT=5
SELLER_WITHDRAW_FEE_PERCENT=5
MIN_WITHDRAW_AMOUNT=50000
```

### 4. Cấu hình Apache

Đảm bảo file `.htaccess` đã được upload và mod_rewrite được bật.

Nếu website nằm trong thư mục con, cập nhật `.htaccess`:

```apache
RewriteBase /subfolder/
```

### 5. Phân quyền thư mục

Cấp quyền ghi (755 hoặc 777) cho các thư mục:
- `storage/logs`
- `storage/cache`
- `storage/backups`
- `public/assets/uploads`

### 6. Truy cập website

Truy cập: `https://yourdomain.com`

## Tài khoản demo

Sau khi import database, bạn có thể đăng nhập bằng các tài khoản sau:

**Admin:**
- Email: `admin@example.com`
- Password: `123456`

**Seller:**
- Email: `seller@example.com`
- Password: `123456`

**User:**
- Email: `user@example.com`
- Password: `123456`

**Affiliate:**
- Email: `affiliate@example.com`
- Password: `123456`

## Cấu trúc thư mục

```
/
├── app/
│   ├── Controllers/      # Các controller
│   ├── Models/          # Các model
│   ├── Views/           # Các view template
│   ├── Core/            # Core classes (Router, Database, Auth...)
│   └── Services/        # Business logic services
├── config/              # File cấu hình
├── public/              # Document root
│   ├── index.php        # Entry point
│   └── assets/          # CSS, JS, images
├── routes/              # Route definitions
├── storage/             # Logs, cache, backups
├── database.sql         # Database schema
├── .env.example         # Environment config example
└── README.md
```

## Cấu hình Google Login

1. Truy cập [Google Cloud Console](https://console.cloud.google.com/)
2. Tạo project mới
3. Bật Google+ API
4. Tạo OAuth 2.0 credentials
5. Thêm Authorized redirect URIs: `https://yourdomain.com/auth/google/callback`
6. Copy Client ID và Client Secret vào file `.env`

## Quy trình bán hàng

### Seller đăng sản phẩm:
1. Seller đăng nhập
2. Vào "Thêm sản phẩm"
3. Nhập thông tin sản phẩm
4. Upload ảnh
5. Chờ admin duyệt
6. Sau khi được duyệt, vào "Quản lý kho" để nhập key/account

### User mua hàng:
1. User đăng nhập
2. Chọn sản phẩm, thêm vào giỏ
3. Thanh toán bằng ví
4. Nhận sản phẩm ngay lập tức
5. Xem trong "Đơn hàng của tôi"

### Seller rút tiền:
1. Vào "Rút tiền"
2. Nhập số tiền muốn rút
3. Chọn phương thức (Bank/Momo/USDT)
4. Nhập thông tin tài khoản
5. Gửi yêu cầu
6. Chờ admin duyệt

## Công thức tính phí

### Phí đơn hàng (mặc định 5%):
- User mua sản phẩm: 100,000đ
- Seller nhận: 95,000đ (95%)
- Admin nhận: 5,000đ (5%)

### Phí rút tiền (mặc định 5%):
- Seller rút: 100,000đ
- Phí rút: 5,000đ (5%)
- Seller thực nhận: 95,000đ
- Admin nhận phí: 5,000đ

## Bảo mật

- ✅ Password được hash bằng `password_hash()`
- ✅ CSRF protection cho tất cả form
- ✅ PDO Prepared Statements chống SQL Injection
- ✅ XSS protection với `htmlspecialchars()`
- ✅ Rate limiting cho login
- ✅ Session security
- ✅ File upload validation

## Tối ưu hiệu năng

- Database indexes cho các trường thường query
- Phân trang cho danh sách dài
- Lazy loading images
- Minify CSS/JS khi production
- Tương thích với LiteSpeed Cache

## Hỗ trợ

Nếu gặp vấn đề, vui lòng kiểm tra:

1. PHP version >= 8.1
2. Database connection trong `.env`
3. Quyền ghi thư mục `storage` và `public/assets/uploads`
4. Apache mod_rewrite đã bật
5. File `.htaccess` tồn tại

## License

MIT License - Free to use for commercial projects

## Credits

Developed by AI Assistant
Version: 1.0.0
Date: 2026-04-30
