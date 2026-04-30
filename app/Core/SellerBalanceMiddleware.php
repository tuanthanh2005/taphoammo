<?php
// app/Core/SellerBalanceMiddleware.php
// Đã vô hiệu hóa - không còn bắt buộc minimum balance

class SellerBalanceMiddleware extends Middleware {
    public function handle() {
        return true; // Không chặn gì cả
    }
}
