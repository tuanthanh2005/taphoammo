<?php
// app/Core/Middleware.php

class Middleware {
    public function handle() {
        return true;
    }
}

class AuthMiddleware extends Middleware {
    public function handle() {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
        return true;
    }
}

class GuestMiddleware extends Middleware {
    public function handle() {
        if (Auth::check()) {
            header('Location: /');
            exit;
        }
        return true;
    }
}

class AdminMiddleware extends Middleware {
    public function handle() {
        if (!Auth::isAdmin()) {
            http_response_code(403);
            die('403 - Forbidden');
        }
        return true;
    }
}

class SellerMiddleware extends Middleware {
    public function handle() {
        if (!Auth::isSeller()) {
            http_response_code(403);
            die('403 - Forbidden');
        }
        return true;
    }
}

class AffiliateMiddleware extends Middleware {
    public function handle() {
        if (!Auth::isAffiliate()) {
            http_response_code(403);
            die('403 - Forbidden');
        }
        return true;
    }
}
