<?php
// config/payment.php

return [
    'admin_order_fee_percent' => $_ENV['ADMIN_ORDER_FEE_PERCENT'] ?? 5,
    'seller_withdraw_fee_percent' => $_ENV['SELLER_WITHDRAW_FEE_PERCENT'] ?? 5,
    'min_withdraw_amount' => $_ENV['MIN_WITHDRAW_AMOUNT'] ?? 50000,
];
