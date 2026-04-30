# Hướng Dẫn Hệ Thống Escrow/Deposit

## 🎯 Mục Đích

Hệ thống Escrow/Deposit được thiết kế để bảo vệ cả **Buyer** và **Platform** khỏi rủi ro khi seller bỏ chạy sau khi nhận tiền.

## 🔄 Luồng Hoạt Động

### 1. Seller Nhập Stock (Phải Đặt Cọc)

```
Seller nhập 100 sản phẩm @ 100,000đ/sp
→ Tổng giá trị: 10,000,000đ
→ Tiền cọc (10%): 1,000,000đ
→ Trừ từ ví seller: 1,000,000đ
→ Chuyển vào deposit_balance
```

**Lợi ích:**
- Seller phải có "skin in the game"
- Không thể nhập stock rồi bỏ chạy
- Đảm bảo seller có trách nhiệm với sản phẩm

### 2. Khách Mua Hàng (Tiền Bị Giữ 7 Ngày)

```
Buyer mua 1 sản phẩm @ 100,000đ
→ Admin fee (5%): 5,000đ
→ Seller nhận: 95,000đ
→ NHƯNG tiền bị HOLD (giữ) 7 ngày
→ Chuyển vào held_balance (không rút được)
```

**Lợi ích:**
- Buyer có 7 ngày để khiếu nại nếu sản phẩm lỗi
- Seller không thể rút tiền ngay → không thể bỏ chạy
- Admin có thời gian xử lý tranh chấp

### 3. Sau 7 Ngày (Auto Release)

```
Cron job chạy mỗi giờ
→ Kiểm tra held_funds có hold_until <= NOW()
→ Chuyển từ held_balance → balance
→ Seller có thể rút tiền
```

**Lợi ích:**
- Tự động hóa, không cần admin can thiệp
- Seller nhận tiền sau khi đảm bảo không có vấn đề
- Giảm rủi ro tranh chấp

### 4. Nếu Có Khiếu Nại

```
Buyer khiếu nại trong 7 ngày
→ Admin xem xét
→ Nếu seller sai:
   - Hoàn tiền cho buyer từ held_balance
   - Trừ thêm từ deposit_balance nếu cần
→ Nếu buyer sai:
   - Release tiền cho seller ngay
```

## 📊 Cấu Trúc Database

### Bảng `seller_deposits`
```sql
- id
- seller_id
- product_id
- stock_quantity (số lượng nhập)
- product_value (tổng giá trị)
- deposit_amount (tiền cọc)
- deposit_percentage (% cọc, mặc định 10%)
- status (pending/paid/released/refunded)
- paid_at, released_at
```

### Bảng `held_funds`
```sql
- id
- order_id
- seller_id
- amount (số tiền giữ)
- hold_until (giữ đến ngày)
- status (holding/released/refunded)
- released_at
```

### Bảng `wallets` (thêm cột)
```sql
- balance (số dư khả dụng - có thể rút)
- held_balance (tiền đang giữ - KHÔNG rút được)
- deposit_balance (tiền cọc - KHÔNG rút được)
```

### Bảng `system_settings`
```sql
- deposit_percentage: 10 (%)
- hold_days: 7 (ngày)
- min_deposit_amount: 50000 (VNĐ)
- enable_escrow: 1 (bật/tắt)
```

## 🔧 Cấu Hình

### Bật/Tắt Hệ Thống
```sql
UPDATE system_settings 
SET setting_value = '1' 
WHERE setting_key = 'enable_escrow';
-- 1 = bật, 0 = tắt
```

### Thay Đổi % Cọc
```sql
UPDATE system_settings 
SET setting_value = '15' 
WHERE setting_key = 'deposit_percentage';
-- Mặc định: 10%
```

### Thay Đổi Thời Gian Giữ Tiền
```sql
UPDATE system_settings 
SET setting_value = '14' 
WHERE setting_key = 'hold_days';
-- Mặc định: 7 ngày
```

## 🚀 Cài Đặt

### Bước 1: Chạy Migration
```bash
php install_escrow_system.php
```

### Bước 2: Setup Cron Job
```bash
# Chạy mỗi giờ để auto-release tiền
0 * * * * php /path/to/cron_release_funds.php >> /path/to/logs/cron.log 2>&1
```

### Bước 3: Kiểm Tra
```bash
# Test seller nhập stock
1. Đăng nhập seller
2. Vào sản phẩm → Quản lý stock
3. Nhập 10 mã hàng
4. Kiểm tra ví → Tiền cọc đã trừ

# Test buyer mua hàng
1. Đăng nhập buyer
2. Mua sản phẩm
3. Kiểm tra ví seller → Tiền vào held_balance

# Test auto-release
1. Chạy: php cron_release_funds.php
2. Hoặc đợi 7 ngày
3. Kiểm tra ví seller → Tiền chuyển sang balance
```

## 📈 Ví Dụ Thực Tế

### Scenario 1: Seller Trung Thực
```
Day 0: Seller nhập 100 key game @ 50k
       → Cọc: 500k (10% của 5M)
       
Day 1: Buyer mua 10 key = 500k
       → Seller nhận: 475k (sau trừ 5% admin fee)
       → Tiền vào held_balance
       
Day 8: Auto-release
       → 475k chuyển sang balance
       → Seller rút tiền thành công
       
Day 30: Seller bán hết 100 key
        → Tổng doanh thu: 4.75M (sau admin fee)
        → Hoàn lại tiền cọc: 500k
        → Tổng nhận: 5.25M
```

### Scenario 2: Seller Lừa Đảo
```
Day 0: Seller nhập 100 key FAKE @ 50k
       → Cọc: 500k
       
Day 1: Buyer mua 10 key = 500k
       → Seller nhận: 475k (held)
       → Key không hoạt động!
       
Day 2: Buyer khiếu nại
       → Admin xác nhận key fake
       → Hoàn 500k cho buyer từ held_balance
       → Trừ thêm 25k từ deposit_balance
       → Ban seller
       
Kết quả: Seller mất 525k, không lấy được tiền
```

### Scenario 3: Seller Bỏ Chạy
```
Day 0: Seller nhập 100 key @ 50k
       → Cọc: 500k
       
Day 1-7: Bán được 50 key = 2.5M
         → Nhận: 2.375M (held)
         → Seller muốn rút → KHÔNG ĐƯỢC (đang hold)
         
Day 8: Seller xóa tài khoản, bỏ chạy
       → Tiền vẫn trong held_balance
       → Admin có thể hoàn cho buyers nếu có vấn đề
       → Tiền cọc 500k bị tịch thu
       
Kết quả: Seller không lấy được tiền, buyers được bảo vệ
```

## 💡 Lợi Ích

### Cho Platform
- ✅ Giảm rủi ro lừa đảo
- ✅ Tăng uy tín
- ✅ Giảm tranh chấp
- ✅ Tự động hóa xử lý

### Cho Seller Trung Thực
- ✅ Tăng độ tin cậy
- ✅ Nhận tiền sau 7 ngày (vẫn OK)
- ✅ Lấy lại tiền cọc khi hết bán
- ✅ Ít bị khiếu nại vô lý

### Cho Buyer
- ✅ An tâm mua hàng
- ✅ Có 7 ngày để kiểm tra
- ✅ Dễ dàng khiếu nại nếu lỗi
- ✅ Được hoàn tiền nếu seller lừa đảo

## ⚠️ Lưu Ý

1. **Tiền cọc KHÔNG hoàn lại tự động** - Seller phải liên hệ admin để lấy lại sau khi ngừng bán
2. **Held funds tự động release sau 7 ngày** - Không cần admin can thiệp
3. **Seller cần đủ tiền trong ví** để nhập stock (cho tiền cọc)
4. **Cron job phải chạy đều đặn** để auto-release hoạt động
5. **Admin có thể manual release** nếu cần thiết

## 🔐 Bảo Mật

- Tất cả transaction đều có log
- Không thể bypass hệ thống hold
- Admin có quyền can thiệp khi cần
- Tiền cọc và held funds tách biệt với balance

## 📞 Hỗ Trợ

Nếu có vấn đề:
1. Kiểm tra `system_settings` - đảm bảo `enable_escrow = 1`
2. Kiểm tra cron job có chạy không
3. Xem log trong `transactions` table
4. Kiểm tra `held_funds` và `seller_deposits` tables

---

**Phiên bản**: 1.0.0  
**Ngày tạo**: 2026-04-30  
**Tương thích**: PHP 7.4+, MySQL 5.7+
