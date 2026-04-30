# Hướng dẫn Deploy lên Hostinger

## Cấu trúc thư mục trên Hostinger

```
/home/u828928906/                    ← Home directory
├── domains/
│   └── yourdomain.com/
│       └── public_html/             ← Document root (chỉ chứa file public)
│           ├── index.php
│           ├── .htaccess
│           └── assets/
│               ├── css/
│               ├── js/
│               ├── images/
│               └── uploads/
├── app/                             ← Code PHP (bảo mật, ngoài public_html)
├── config/
├── routes/
├── storage/
├── .env
└── database.sql
```

## BƯỚC 1: Upload Files

### 1.1. Kết nối FTP/SFTP
- Host: ftp.yourdomain.com
- Username: u828928906
- Password: your_password
- Port: 21 (FTP) hoặc 22 (SFTP)

### 1.2. Upload cấu trúc

**A. Upload vào thư mục HOME (/home/u828928906/):**
```
app/
config/
routes/
storage/
.env
database.sql
```

**B. Upload vào public_html:**
```
Chỉ upload nội dung bên trong thư mục public/:
- index.php
- .htaccess
- assets/ (toàn bộ)
```

### Cấu trúc sau khi upload:

```
/home/u828928906/
├── app/
├── config/
├── routes/
├── storage/
├── .env
├── database.sql
└── domains/
    └── yourdomain.com/
        └── public_html/
            ├── index.php
            ├── .htaccess
            └── assets/
```

## BƯỚC 2: Tạo Database

1. Đăng nhập Hostinger hPanel
2. Vào **Databases** → **MySQL Databases**
3. Tạo database mới:
   - Database name: `u828928906_mmo`
   - Username: `u828928906_user`
   - Password: (tạo password mạnh)
4. Vào **phpMyAdmin**
5. Chọn database `u828928906_mmo`
6. Click **Import**
7. Chọn file `database.sql`
8. Click **Go**

## BƯỚC 3: Cấu hình .env

Chỉnh sửa file `.env` trong `/home/u828928906/.env`:

```env
APP_NAME="MMO Marketplace"
APP_URL="https://yourdomain.com"
APP_ENV=production
APP_DEBUG=false

DB_HOST=localhost
DB_NAME=u828928906_mmo
DB_USER=u828928906_user
DB_PASS=your_database_password

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback

ADMIN_ORDER_FEE_PERCENT=5
SELLER_WITHDRAW_FEE_PERCENT=5
MIN_WITHDRAW_AMOUNT=50000
```

## BƯỚC 4: Cấu hình .htaccess

### File .htaccess trong public_html:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Redirect to index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

# Bảo vệ file .env
<Files .env>
    Order allow,deny
    Deny from all
</Files>

# Disable directory listing
Options -Indexes

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

## BƯỚC 5: Phân quyền thư mục

Qua File Manager hoặc SSH:

```bash
chmod 755 /home/u828928906/storage
chmod 755 /home/u828928906/storage/logs
chmod 755 /home/u828928906/storage/cache
chmod 755 /home/u828928906/storage/backups
chmod 755 /home/u828928906/domains/yourdomain.com/public_html/assets/uploads
```

Hoặc qua File Manager:
- Click phải vào thư mục
- Chọn **Permissions**
- Set: `755` (rwxr-xr-x)

## BƯỚC 6: Cấu hình SSL

1. Vào hPanel
2. Chọn **SSL**
3. Click **Install SSL** cho domain
4. Chọn **Let's Encrypt** (Free)
5. Đợi vài phút để SSL active

## BƯỚC 7: Test Website

1. Truy cập: `https://yourdomain.com`
2. Đăng nhập demo:
   - Admin: `admin@example.com` / `123456`
   - Seller: `seller@example.com` / `123456`

## BƯỚC 8: Bảo mật

### 8.1. Đổi mật khẩu admin
```sql
-- Truy cập phpMyAdmin
UPDATE users 
SET password = '$2y$10$NEW_HASHED_PASSWORD' 
WHERE email = 'admin@example.com';
```

### 8.2. Xóa tài khoản demo
```sql
DELETE FROM users 
WHERE email IN ('seller@example.com', 'user@example.com', 'affiliate@example.com');
```

### 8.3. Bảo vệ file .env
File `.env` nằm ngoài `public_html` nên đã an toàn.

## BƯỚC 9: Tối ưu hiệu năng

### 9.1. Bật LiteSpeed Cache
1. Vào hPanel
2. Chọn **Advanced** → **LiteSpeed Cache**
3. Enable cache

### 9.2. Tối ưu database
```sql
OPTIMIZE TABLE users, products, orders, order_items, transactions;
```

### 9.3. Minify CSS/JS (Optional)
Sử dụng online tools để minify:
- https://cssminifier.com/
- https://javascript-minifier.com/

## BƯỚC 10: Backup tự động

### Tạo Cronjob backup:

1. Vào hPanel → **Advanced** → **Cron Jobs**
2. Tạo cronjob mới:

**Command:**
```bash
/usr/bin/mysqldump -u u828928906_user -p'password' u828928906_mmo > /home/u828928906/backup_$(date +\%Y\%m\%d).sql
```

**Schedule:** Hàng ngày lúc 2:00 AM
```
0 2 * * *
```

## Troubleshooting

### Lỗi 500 Internal Server Error
1. Kiểm tra file `.htaccess`
2. Kiểm tra quyền thư mục (755)
3. Xem error log: hPanel → **Files** → **Error Log**

### Lỗi database connection
1. Kiểm tra thông tin trong `.env`
2. Kiểm tra database user có quyền truy cập
3. Test connection qua phpMyAdmin

### Không upload được ảnh
1. Kiểm tra quyền thư mục `public_html/assets/uploads/` (755)
2. Kiểm tra `upload_max_filesize` trong PHP settings
3. Vào hPanel → **Advanced** → **PHP Configuration**

### Lỗi 404 Not Found
1. Kiểm tra mod_rewrite đã bật
2. Kiểm tra file `.htaccess` trong `public_html`
3. Clear browser cache

## Kiểm tra PHP Settings

Tạo file `info.php` trong `public_html`:
```php
<?php phpinfo(); ?>
```

Truy cập: `https://yourdomain.com/info.php`

Kiểm tra:
- PHP Version: >= 8.1
- PDO: Enabled
- PDO MySQL: Enabled
- upload_max_filesize: >= 5M
- post_max_size: >= 10M

**Xóa file info.php sau khi kiểm tra!**

## Support

Nếu gặp vấn đề:
1. Kiểm tra Error Log trong hPanel
2. Kiểm tra file `storage/logs/`
3. Contact Hostinger Support

---

**Chúc bạn deploy thành công! 🚀**
