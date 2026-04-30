# Hướng dẫn Deploy lên Hosting

## Bước 1: Chuẩn bị

### Yêu cầu hosting:
- PHP 8.1+
- MySQL 5.7+ hoặc MariaDB 10.3+
- Apache với mod_rewrite
- SSL certificate (khuyến nghị)

### Kiểm tra trước khi deploy:
```bash
php -v  # Kiểm tra PHP version
```

## Bước 2: Upload Files

### Sử dụng FTP/SFTP:
1. Kết nối tới hosting qua FileZilla hoặc WinSCP
2. Upload toàn bộ files vào thư mục `public_html` hoặc `www`

### Sử dụng Git (nếu hosting hỗ trợ):
```bash
git clone your-repository-url
```

## Bước 3: Tạo Database

1. Đăng nhập vào cPanel
2. Vào **MySQL Databases**
3. Tạo database mới (ví dụ: `mmo_marketplace`)
4. Tạo user và gán quyền ALL PRIVILEGES
5. Vào **phpMyAdmin**
6. Chọn database vừa tạo
7. Import file `database.sql`

## Bước 4: Cấu hình .env

1. Đổi tên `.env.example` thành `.env`
2. Cập nhật thông tin:

```env
APP_NAME="MMO Marketplace"
APP_URL="https://yourdomain.com"
APP_ENV=production
APP_DEBUG=false

DB_HOST=localhost
DB_NAME=mmo_marketplace
DB_USER=your_db_user
DB_PASS=your_db_password

ADMIN_ORDER_FEE_PERCENT=5
SELLER_WITHDRAW_FEE_PERCENT=5
MIN_WITHDRAW_AMOUNT=50000
```

## Bước 5: Cấu hình Apache

### Nếu website ở root domain:

File `.htaccess` trong root:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php [QSA,L]
```

File `public/.htaccess`:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Nếu website ở subdomain hoặc subfolder:

Cập nhật `.htaccess` với RewriteBase:
```apache
RewriteEngine On
RewriteBase /subfolder/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php [QSA,L]
```

## Bước 6: Phân quyền thư mục

Cấp quyền ghi (755 hoặc 777) cho:

```bash
chmod -R 755 storage/
chmod -R 755 public/assets/uploads/
```

Hoặc qua cPanel File Manager:
- Click phải vào thư mục
- Chọn "Change Permissions"
- Tick: Read, Write, Execute cho Owner
- Tick: Read, Execute cho Group và Public

## Bước 7: Cấu hình SSL (HTTPS)

### Sử dụng Let's Encrypt (Free):
1. Vào cPanel
2. Tìm "SSL/TLS Status"
3. Chọn domain
4. Click "Run AutoSSL"

### Force HTTPS:
Thêm vào `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## Bước 8: Cấu hình Google Login (Optional)

1. Truy cập [Google Cloud Console](https://console.cloud.google.com/)
2. Tạo project mới
3. Bật Google+ API
4. Tạo OAuth 2.0 Client ID
5. Thêm Authorized redirect URIs:
   ```
   https://yourdomain.com/auth/google/callback
   ```
6. Copy Client ID và Secret vào `.env`

## Bước 9: Test Website

1. Truy cập: `https://yourdomain.com`
2. Đăng nhập bằng tài khoản demo:
   - Admin: `admin@example.com` / `123456`
   - Seller: `seller@example.com` / `123456`
3. Test các chức năng:
   - Đăng ký user mới
   - Thêm sản phẩm (seller)
   - Mua hàng
   - Rút tiền

## Bước 10: Bảo mật

### Đổi mật khẩu admin:
1. Đăng nhập admin
2. Vào Profile
3. Đổi mật khẩu mạnh

### Xóa tài khoản demo:
```sql
DELETE FROM users WHERE email IN ('seller@example.com', 'user@example.com', 'affiliate@example.com');
```

### Tắt debug mode:
```env
APP_DEBUG=false
```

### Bảo vệ file .env:
Thêm vào `.htaccess`:
```apache
<Files .env>
    Order allow,deny
    Deny from all
</Files>
```

## Bước 11: Tối ưu hiệu năng

### Bật Gzip compression:
Thêm vào `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

### Bật Browser caching:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### Tối ưu database:
```sql
OPTIMIZE TABLE users, products, orders, transactions;
```

## Bước 12: Backup

### Backup tự động (Cronjob):
Tạo file `backup.sh`:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p'password' database_name > backup_$DATE.sql
tar -czf backup_$DATE.tar.gz backup_$DATE.sql public/assets/uploads/
rm backup_$DATE.sql
```

Thêm vào crontab (chạy hàng ngày lúc 2h sáng):
```
0 2 * * * /path/to/backup.sh
```

## Troubleshooting

### Lỗi 500 Internal Server Error:
- Kiểm tra file `.htaccess`
- Kiểm tra quyền thư mục
- Xem error log: `storage/logs/`

### Lỗi database connection:
- Kiểm tra thông tin trong `.env`
- Kiểm tra user có quyền truy cập database

### Lỗi 404 Not Found:
- Kiểm tra mod_rewrite đã bật
- Kiểm tra file `.htaccess`

### Không upload được ảnh:
- Kiểm tra quyền thư mục `public/assets/uploads/`
- Kiểm tra `upload_max_filesize` trong php.ini

## Support

Nếu gặp vấn đề, vui lòng kiểm tra:
1. PHP error log
2. Apache error log
3. File `storage/logs/`

---

**Chúc bạn deploy thành công! 🚀**
