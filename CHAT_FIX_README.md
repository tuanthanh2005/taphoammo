# Hướng Dẫn Sửa Lỗi Hệ Thống Chat

## Vấn Đề Đã Được Khắc Phục

### 1. **Lỗi nút gửi tin nhắn bị vô hiệu hóa sau 3 tin nhắn**
   - **Nguyên nhân**: Button không được re-enable sau khi gửi tin nhắn
   - **Giải pháp**: Thêm logic `finally` block để luôn re-enable button sau khi gửi

### 2. **Lỗi database schema không khớp**
   - **Nguyên nhân**: 
     - Tên cột trong database (`user_id`, `user_unread`, `seller_unread`) không khớp với code (`buyer_id`, `unread_count_buyer`, `unread_count_seller`)
     - Thiếu cột `last_active_at` trong bảng `users` để theo dõi trạng thái online
     - Cột `receiver_id` không cần thiết trong bảng `messages`
   - **Giải pháp**: Cập nhật schema và tạo script migration tự động

### 3. **Tin nhắn không hiển thị liên tục**
   - **Nguyên nhân**: Race condition khi gửi và load tin nhắn
   - **Giải pháp**: API trả về danh sách tin nhắn ngay sau khi gửi thành công

## Các File Đã Được Cập Nhật

### 1. **database_chat_update.sql**
   - Đổi `user_id` → `buyer_id`
   - Đổi `user_unread` → `unread_count_buyer`
   - Đổi `seller_unread` → `unread_count_seller`
   - Xóa cột `receiver_id` không cần thiết
   - Thêm cột `last_active_at` vào bảng `users`

### 2. **app/Controllers/ChatController.php**
   - Cải thiện logic xử lý tin nhắn
   - Trả về danh sách tin nhắn sau khi gửi thành công
   - Thêm validation tốt hơn

### 3. **app/Views/products/show.php**
   - Sửa hàm `sendChatMessage()` để:
     - Disable button trong khi gửi
     - Hiển thị spinner loading
     - Luôn re-enable button trong `finally` block
     - Cập nhật tin nhắn ngay lập tức từ response
     - Hiển thị thông báo lỗi dạng toast

### 4. **app/Core/Auth.php**
   - Thêm method `updateLastActive()` để cập nhật thời gian hoạt động
   - Tự động gọi khi user login

### 5. **app/Views/layouts/header.php**
   - Tự động cập nhật `last_active_at` mỗi khi load trang
   - Giúp theo dõi trạng thái online/offline

### 6. **update_chat_schema.php** (MỚI)
   - Script tự động cập nhật database schema
   - Kiểm tra và đổi tên cột nếu cần
   - Thêm cột `last_active_at` nếu chưa có
   - Xóa cột không cần thiết

## Hướng Dẫn Cài Đặt

### Bước 1: Cập nhật Database

Chạy script tự động:

```bash
php update_chat_schema.php
```

**HOẶC** chạy SQL thủ công trong phpMyAdmin:

```sql
-- Nếu bảng chưa tồn tại, chạy file database_chat_update.sql
-- Nếu bảng đã tồn tại, chạy các lệnh sau:

-- Đổi tên cột trong bảng conversations
ALTER TABLE conversations CHANGE `user_id` `buyer_id` int(11) NOT NULL COMMENT 'Người mua';
ALTER TABLE conversations CHANGE `user_unread` `unread_count_buyer` int(11) DEFAULT 0;
ALTER TABLE conversations CHANGE `seller_unread` `unread_count_seller` int(11) DEFAULT 0;

-- Xóa cột không cần thiết trong bảng messages
ALTER TABLE messages DROP FOREIGN KEY IF EXISTS fk_msg_receiver;
ALTER TABLE messages DROP COLUMN IF EXISTS `receiver_id`;

-- Thêm cột last_active_at vào bảng users
ALTER TABLE users ADD COLUMN `last_active_at` DATETIME DEFAULT NULL AFTER `updated_at`;
```

### Bước 2: Kiểm Tra Hoạt Động

1. **Đăng nhập vào hệ thống**
2. **Vào trang sản phẩm bất kỳ**
3. **Click nút "Nhắn tin với shop"**
4. **Gửi nhiều tin nhắn liên tục** (>3 tin nhắn)
5. **Kiểm tra**:
   - ✅ Nút gửi không bị disable
   - ✅ Tin nhắn hiển thị ngay lập tức
   - ✅ Có thể gửi tin nhắn liên tục không giới hạn
   - ✅ Trạng thái online/offline hiển thị chính xác

### Bước 3: Test Với Seller

1. **Đăng nhập tài khoản seller**
2. **Vào `/seller/chat`**
3. **Chọn cuộc trò chuyện**
4. **Gửi tin nhắn liên tục**
5. **Kiểm tra tương tự như trên**

## Tính Năng Mới

### 1. **Trạng Thái Online/Offline**
   - Hiển thị chấm xanh nếu user online (hoạt động trong 5 phút gần nhất)
   - Hiển thị "Đang trực tuyến" hoặc "Ngoại tuyến"
   - Tự động cập nhật mỗi khi user load trang

### 2. **Loading Indicator**
   - Hiển thị spinner khi đang gửi tin nhắn
   - Disable button để tránh gửi trùng lặp
   - Luôn re-enable sau khi hoàn thành

### 3. **Toast Notifications**
   - Thông báo lỗi dạng toast ở góc màn hình
   - Tự động ẩn sau 3 giây
   - Không làm gián đoạn trải nghiệm người dùng

### 4. **Real-time Message Updates**
   - Tin nhắn cập nhật ngay lập tức sau khi gửi
   - Không cần đợi polling interval (3 giây)
   - Tự động scroll xuống tin nhắn mới nhất

## Cấu Trúc Database

### Bảng `conversations`
```sql
- id (PK)
- buyer_id (FK → users.id)
- seller_id (FK → users.id)
- product_id (FK → products.id, nullable)
- last_message (text)
- last_message_at (datetime)
- unread_count_buyer (int)
- unread_count_seller (int)
- status (enum: active, closed)
- created_at (timestamp)
- updated_at (timestamp)
```

### Bảng `messages`
```sql
- id (PK)
- conversation_id (FK → conversations.id)
- sender_id (FK → users.id)
- message (text)
- is_read (boolean)
- read_at (datetime)
- created_at (timestamp)
```

### Bảng `users` (cột mới)
```sql
- last_active_at (datetime) -- Thời gian hoạt động gần nhất
```

## API Endpoints

### 1. **GET /api/chat/messages**
   - **Params**: `seller_id`
   - **Response**: Danh sách tin nhắn giữa buyer và seller
   - **Tự động**: Tạo conversation nếu chưa có

### 2. **POST /api/chat/send**
   - **Body**: `seller_id` hoặc `conversation_id`, `message`, `csrf_token`
   - **Response**: Danh sách tin nhắn đã cập nhật
   - **Tự động**: Cập nhật last_message và unread_count

### 3. **GET /api/chat/conversation**
   - **Params**: `id` (conversation_id)
   - **Response**: Tin nhắn của cuộc trò chuyện cụ thể
   - **Tự động**: Đánh dấu đã đọc

### 4. **GET /api/chat/list**
   - **Response**: Danh sách tất cả cuộc trò chuyện của user
   - **Include**: Thông tin người chat, tin nhắn cuối, số tin chưa đọc

## Troubleshooting

### Lỗi: "Column 'buyer_id' not found"
**Giải pháp**: Chạy lại `update_chat_schema.php` hoặc SQL update thủ công

### Lỗi: "Table 'conversations' doesn't exist"
**Giải pháp**: Import file `database_chat_update.sql` vào database

### Nút gửi vẫn bị disable
**Giải pháp**: 
1. Clear cache trình duyệt (Ctrl + Shift + R)
2. Kiểm tra Console có lỗi JavaScript không
3. Đảm bảo đã cập nhật file `app/Views/products/show.php`

### Trạng thái online không chính xác
**Giải pháp**:
1. Kiểm tra cột `last_active_at` đã được thêm vào bảng `users`
2. Kiểm tra file `app/Views/layouts/header.php` đã có code update last active
3. Đăng xuất và đăng nhập lại

## Lưu Ý Quan Trọng

1. **Backup database trước khi chạy update script**
2. **Test trên môi trường local trước khi deploy production**
3. **Đảm bảo tất cả file đã được cập nhật đúng version**
4. **Clear cache trình duyệt sau khi update**

## Liên Hệ Hỗ Trợ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra lại các bước cài đặt
2. Xem log lỗi trong Console (F12)
3. Kiểm tra database schema đã đúng chưa
4. Đảm bảo server PHP đang chạy

---

**Phiên bản**: 1.0.0  
**Ngày cập nhật**: 2026-04-30  
**Tương thích**: PHP 7.4+, MySQL 5.7+
