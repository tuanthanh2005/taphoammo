# ✅ CHECKLIST DEPLOY LÊN HOSTINGER

## Trước khi deploy

- [ ] Đã có tài khoản Hostinger
- [ ] Đã trỏ domain về Hostinger
- [ ] Đã có thông tin FTP/SFTP
- [ ] Đã backup code hiện tại

## Upload Files

- [ ] Upload `app/` vào `/home/u828928906/app/`
- [ ] Upload `config/` vào `/home/u828928906/config/`
- [ ] Upload `routes/` vào `/home/u828928906/routes/`
- [ ] Upload `storage/` vào `/home/u828928906/storage/`
- [ ] Upload `.env` vào `/home/u828928906/.env`
- [ ] Upload `database.sql` vào `/home/u828928906/database.sql`
- [ ] Upload `public/index.php` vào `public_html/index.php`
- [ ] Upload `public/.htaccess` vào `public_html/.htaccess`
- [ ] Upload `public/assets/` vào `public_html/assets/`

## Database

- [ ] Tạo database mới trong hPanel
- [ ] Tạo database user
- [ ] Import file `database.sql`
- [ ] Test connection qua phpMyAdmin

## Cấu hình

- [ ] Cập nhật `.env` với thông tin database
- [ ] Cập nhật `APP_URL` trong `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Kiểm tra file `.htaccess` trong `public_html`

## Phân quyền

- [ ] Set quyền 755 cho `storage/`
- [ ] Set quyền 755 cho `storage/logs/`
- [ ] Set quyền 755 cho `storage/cache/`
- [ ] Set quyền 755 cho `storage/backups/`
- [ ] Set quyền 755 cho `public_html/assets/uploads/`

## SSL

- [ ] Cài đặt SSL certificate (Let's Encrypt)
- [ ] Test HTTPS hoạt động
- [ ] Force HTTPS trong `.htaccess`

## Test chức năng

- [ ] Truy cập trang chủ: `https://yourdomain.com`
- [ ] Test đăng nhập admin
- [ ] Test đăng nhập seller
- [ ] Test đăng nhập user
- [ ] Test đăng ký user mới
- [ ] Test upload ảnh sản phẩm
- [ ] Test thêm sản phẩm (seller)
- [ ] Test mua hàng
- [ ] Test thanh toán
- [ ] Test rút tiền
- [ ] Test responsive mobile

## Bảo mật

- [ ] Đổi mật khẩu admin
- [ ] Xóa tài khoản demo
- [ ] Xóa file `info.php` (nếu có)
- [ ] Kiểm tra file `.env` không public
- [ ] Test CSRF protection
- [ ] Test SQL injection prevention

## Tối ưu

- [ ] Bật LiteSpeed Cache
- [ ] Optimize database tables
- [ ] Minify CSS/JS (optional)
- [ ] Test tốc độ load trang
- [ ] Test trên GTmetrix/PageSpeed

## Backup

- [ ] Setup cronjob backup database
- [ ] Test backup hoạt động
- [ ] Lưu backup local

## Google Login (Optional)

- [ ] Tạo OAuth Client trên Google Cloud
- [ ] Thêm redirect URI
- [ ] Cập nhật credentials vào `.env`
- [ ] Test đăng nhập Google

## Monitoring

- [ ] Setup error monitoring
- [ ] Kiểm tra error logs
- [ ] Setup uptime monitoring (optional)

## Documentation

- [ ] Lưu thông tin database
- [ ] Lưu thông tin FTP
- [ ] Lưu thông tin admin
- [ ] Tạo tài liệu hướng dẫn sử dụng

## Go Live

- [ ] Thông báo cho team
- [ ] Update DNS (nếu cần)
- [ ] Monitor trong 24h đầu
- [ ] Sẵn sàng rollback nếu có lỗi

---

## Thông tin quan trọng cần lưu

```
Domain: https://yourdomain.com
FTP Host: ftp.yourdomain.com
FTP User: u828928906
FTP Pass: ***********

Database Host: localhost
Database Name: u828928906_mmo
Database User: u828928906_user
Database Pass: ***********

Admin Email: admin@example.com
Admin Pass: ***********

Hostinger hPanel: https://hpanel.hostinger.com
```

---

**Sau khi hoàn thành checklist, website đã sẵn sàng!** 🎉
