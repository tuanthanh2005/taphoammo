# Hướng Dẫn Test Hệ Thống Chat

## Chuẩn Bị

1. **Đảm bảo server đang chạy**:
   ```bash
   cd public
   php -S localhost:8000
   ```

2. **Đảm bảo database đã được cập nhật**:
   - Đã chạy `php update_chat_schema.php` thành công
   - Hoặc đã import `database_chat_update.sql`

## Test Case 1: Chat Từ Trang Sản Phẩm (Buyer → Seller)

### Bước 1: Đăng nhập tài khoản buyer
- URL: `http://localhost:8000/login`
- Username: `buyer` hoặc `user`
- Password: `123456`

### Bước 2: Vào trang sản phẩm
- URL: `http://localhost:8000/products`
- Click vào bất kỳ sản phẩm nào

### Bước 3: Mở chat modal
- Click nút **"Nhắn tin với shop"** (icon comment màu xanh)
- Modal chat sẽ hiển thị

### Bước 4: Gửi tin nhắn liên tục
1. Gõ tin nhắn: "Xin chào"
2. Click nút gửi hoặc nhấn Enter
3. ✅ **Kiểm tra**: Tin nhắn hiển thị ngay lập tức
4. ✅ **Kiểm tra**: Nút gửi không bị disable
5. Gửi tiếp tin nhắn thứ 2: "Sản phẩm còn hàng không?"
6. ✅ **Kiểm tra**: Vẫn gửi được bình thường
7. Gửi tiếp tin nhắn thứ 3, 4, 5, 6...
8. ✅ **Kiểm tra**: Có thể gửi không giới hạn

### Bước 5: Kiểm tra trạng thái online
- ✅ **Kiểm tra**: Hiển thị "Đang trực tuyến" hoặc "Ngoại tuyến"
- ✅ **Kiểm tra**: Có chấm xanh nếu seller online

### Bước 6: Kiểm tra auto-refresh
- Để modal mở trong 3-5 giây
- ✅ **Kiểm tra**: Tin nhắn tự động refresh (nếu có tin mới)

## Test Case 2: Chat Từ Trang Seller (Seller → Buyer)

### Bước 1: Đăng nhập tài khoản seller
- URL: `http://localhost:8000/login`
- Username: `seller`
- Password: `123456`

### Bước 2: Vào trang chat
- URL: `http://localhost:8000/seller/chat`
- Hoặc click icon chat ở header

### Bước 3: Chọn cuộc trò chuyện
- Click vào cuộc trò chuyện với buyer (từ Test Case 1)
- ✅ **Kiểm tra**: Tin nhắn trước đó hiển thị đầy đủ

### Bước 4: Trả lời tin nhắn
1. Gõ: "Dạ còn hàng ạ"
2. Click gửi hoặc Enter
3. ✅ **Kiểm tra**: Tin nhắn hiển thị ngay
4. ✅ **Kiểm tra**: Nút không bị disable
5. Gửi thêm 5-10 tin nhắn liên tục
6. ✅ **Kiểm tra**: Tất cả đều gửi thành công

### Bước 5: Kiểm tra sidebar
- ✅ **Kiểm tra**: Cuộc trò chuyện hiển thị tin nhắn mới nhất
- ✅ **Kiểm tra**: Thời gian cập nhật chính xác
- ✅ **Kiểm tra**: Badge số tin chưa đọc (nếu có)

## Test Case 3: Chat Hai Chiều Real-time

### Bước 1: Mở 2 trình duyệt
- **Trình duyệt 1**: Đăng nhập buyer, mở chat với seller
- **Trình duyệt 2**: Đăng nhập seller, mở `/seller/chat`

### Bước 2: Gửi tin nhắn từ buyer
- Trình duyệt 1: Gửi "Test tin nhắn 1"
- ✅ **Kiểm tra**: Trình duyệt 2 nhận được sau tối đa 3 giây

### Bước 3: Trả lời từ seller
- Trình duyệt 2: Gửi "Đã nhận tin nhắn"
- ✅ **Kiểm tra**: Trình duyệt 1 nhận được sau tối đa 3 giây

### Bước 4: Gửi liên tục từ cả 2 bên
- Gửi 5 tin từ buyer
- Gửi 5 tin từ seller
- ✅ **Kiểm tra**: Tất cả tin nhắn đều hiển thị đúng thứ tự
- ✅ **Kiểm tra**: Không có tin nhắn bị mất

## Test Case 4: Kiểm Tra Trạng Thái Online

### Bước 1: Đăng nhập seller
- Đăng nhập và ở lại trang bất kỳ

### Bước 2: Mở chat từ buyer
- Đăng nhập buyer
- Mở chat với seller vừa online
- ✅ **Kiểm tra**: Hiển thị "Đang trực tuyến" với chấm xanh

### Bước 3: Đăng xuất seller
- Đăng xuất seller
- Đợi 5-10 giây

### Bước 4: Kiểm tra lại từ buyer
- Refresh modal chat hoặc đợi auto-refresh
- ✅ **Kiểm tra**: Hiển thị "Ngoại tuyến"

## Test Case 5: Kiểm Tra Lỗi

### Test 5.1: Gửi tin nhắn rỗng
- Để trống input
- Click gửi
- ✅ **Kiểm tra**: Không gửi được (validation)

### Test 5.2: Gửi tin nhắn khi chưa đăng nhập
- Đăng xuất
- Vào trang sản phẩm
- Click "Nhắn tin với shop"
- ✅ **Kiểm tra**: Hiển thị popup yêu cầu đăng nhập

### Test 5.3: Gửi tin nhắn quá dài
- Gõ tin nhắn > 1000 ký tự
- Click gửi
- ✅ **Kiểm tra**: Vẫn gửi được (hoặc có validation nếu cần)

### Test 5.4: Mất kết nối
- Tắt server PHP
- Thử gửi tin nhắn
- ✅ **Kiểm tra**: Hiển thị thông báo lỗi
- ✅ **Kiểm tra**: Nút vẫn được re-enable

## Test Case 6: Performance

### Test 6.1: Gửi nhanh liên tục
- Gõ và gửi 20 tin nhắn trong 10 giây
- ✅ **Kiểm tra**: Tất cả đều gửi thành công
- ✅ **Kiểm tra**: Không có lag hoặc freeze

### Test 6.2: Cuộc trò chuyện dài
- Tạo cuộc trò chuyện với 100+ tin nhắn
- Mở chat
- ✅ **Kiểm tra**: Load nhanh
- ✅ **Kiểm tra**: Scroll mượt mà
- ✅ **Kiểm tra**: Auto-scroll xuống tin mới nhất

## Checklist Tổng Hợp

### Chức Năng Cơ Bản
- [ ] Gửi tin nhắn thành công
- [ ] Nhận tin nhắn thành công
- [ ] Hiển thị tin nhắn đúng thứ tự
- [ ] Tin nhắn của mình hiển thị bên phải (màu tím)
- [ ] Tin nhắn của người khác hiển thị bên trái (màu trắng)
- [ ] Hiển thị thời gian gửi tin nhắn

### Nút Gửi
- [ ] Nút không bị disable sau khi gửi
- [ ] Có thể gửi >3 tin nhắn liên tục
- [ ] Có thể gửi >10 tin nhắn liên tục
- [ ] Hiển thị spinner khi đang gửi
- [ ] Re-enable ngay sau khi gửi xong

### Trạng Thái Online
- [ ] Hiển thị "Đang trực tuyến" khi online
- [ ] Hiển thị "Ngoại tuyến" khi offline
- [ ] Chấm xanh hiển thị khi online
- [ ] Cập nhật trạng thái tự động

### Auto-Refresh
- [ ] Tin nhắn mới tự động hiển thị sau 3 giây
- [ ] Sidebar cập nhật tin nhắn mới nhất
- [ ] Badge số tin chưa đọc cập nhật

### UI/UX
- [ ] Modal mở mượt mà
- [ ] Scroll tự động xuống tin mới nhất
- [ ] Input focus sau khi gửi
- [ ] Thông báo lỗi dạng toast
- [ ] Responsive trên mobile

### Bảo Mật
- [ ] Không gửi được khi chưa đăng nhập
- [ ] CSRF token được gửi kèm
- [ ] Không thể xem chat của người khác
- [ ] XSS protection (tin nhắn được escape)

## Kết Quả Mong Đợi

✅ **TẤT CẢ** các test case phải PASS  
✅ **KHÔNG CÓ** lỗi trong Console (F12)  
✅ **KHÔNG CÓ** lỗi PHP trong terminal  
✅ **KHÔNG CÓ** nút bị disable vĩnh viễn  
✅ **CÓ THỂ** gửi tin nhắn không giới hạn  

## Nếu Có Lỗi

### Lỗi JavaScript
1. Mở Console (F12)
2. Xem lỗi chi tiết
3. Kiểm tra file `app/Views/products/show.php` và `app/Views/seller/chat.php`

### Lỗi PHP
1. Xem terminal đang chạy server
2. Kiểm tra file `app/Controllers/ChatController.php`
3. Kiểm tra database schema

### Lỗi Database
1. Chạy lại `php update_chat_schema.php`
2. Kiểm tra bảng `conversations`, `messages`, `users`
3. Đảm bảo có cột `last_active_at` trong `users`

---

**Lưu ý**: Nếu tất cả test case đều PASS, hệ thống chat đã hoạt động hoàn hảo! 🎉
