<?php
// app/Controllers/AuthController.php

class AuthController extends Controller {
    
    public function showLogin() {
        $this->view('auth/login');
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }
        
        CSRF::check();
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            Session::setFlash('error', 'Vui lòng nhập đầy đủ thông tin');
            $this->redirect('/login');
            return;
        }
        
        $result = Auth::attempt($email, $password);
        
        if ($result['success']) {
            // Redirect based on role
            $user = $result['user'];
            if ($user['role'] === 'admin') {
                $this->redirect('/admin/dashboard');
            } elseif ($user['role'] === 'seller') {
                $this->redirect('/seller/dashboard');
            } else {
                $this->redirect('/user/dashboard');
            }
        } else {
            Session::setFlash('error', $result['message']);
            $this->redirect('/login');
        }
    }
    
    public function showRegister() {
        $this->view('auth/register');
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register');
            return;
        }
        
        CSRF::check();
        
        $name = $_POST['name'] ?? '';
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($name) || empty($username) || empty($email) || empty($password)) {
            Session::setFlash('error', 'Vui lòng nhập đầy đủ thông tin');
            $this->redirect('/register');
            return;
        }
        
        if ($password !== $confirmPassword) {
            Session::setFlash('error', 'Mật khẩu xác nhận không khớp');
            $this->redirect('/register');
            return;
        }
        
        if (strlen($password) < 6) {
            Session::setFlash('error', 'Mật khẩu phải có ít nhất 6 ký tự');
            $this->redirect('/register');
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Email không hợp lệ');
            $this->redirect('/register');
            return;
        }
        
        $userModel = new User();
        
        // Check if email exists
        if ($userModel->findByEmail($email)) {
            Session::setFlash('error', 'Email đã được sử dụng');
            $this->redirect('/register');
            return;
        }
        
        // Check if username exists
        if ($userModel->findByUsername($username)) {
            Session::setFlash('error', 'Username đã được sử dụng');
            $this->redirect('/register');
            return;
        }
        
        // Check referral code
        $referredBy = null;
        if (!empty($_POST['referral_code'])) {
            $referrer = $userModel->where('referral_code', $_POST['referral_code']);
            if (!empty($referrer)) {
                $referredBy = $referrer[0]['id'];
            }
        } elseif (isset($_COOKIE['ref_code'])) {
            $referrer = $userModel->where('referral_code', $_COOKIE['ref_code']);
            if (!empty($referrer)) {
                $referredBy = $referrer[0]['id'];
            }
        }
        
        // Create user
        $userId = $userModel->createUser([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => 'user',
            'status' => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'referred_by' => $referredBy
        ]);
        
        if ($userId) {
            Session::setFlash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
            $this->redirect('/login');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại');
            $this->redirect('/register');
        }
    }
    
    public function logout() {
        Auth::logout();
        $this->redirect('/');
    }
    
    public function googleRedirect() {
        $config = require __DIR__ . '/../../config/google.php';
        
        if (empty($config['client_id'])) {
            Session::setFlash('error', 'Google login chưa được cấu hình');
            $this->redirect('/login');
            return;
        }
        
        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'online'
        ];
        
        $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        header('Location: ' . $url);
        exit;
    }
    
    public function googleCallback() {
        $config = require __DIR__ . '/../../config/google.php';
        
        if (!isset($_GET['code'])) {
            Session::setFlash('error', 'Đăng nhập Google thất bại');
            $this->redirect('/login');
            return;
        }
        
        // Exchange code for token
        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $tokenData = [
            'code' => $_GET['code'],
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri' => $config['redirect_uri'],
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
        $response = curl_exec($ch);
        curl_close($ch);
        
        $tokenInfo = json_decode($response, true);
        
        if (!isset($tokenInfo['access_token'])) {
            Session::setFlash('error', 'Không thể lấy thông tin từ Google');
            $this->redirect('/login');
            return;
        }
        
        // Get user info
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $tokenInfo['access_token'];
        $userInfo = json_decode(file_get_contents($userInfoUrl), true);
        
        if (!isset($userInfo['email'])) {
            Session::setFlash('error', 'Không thể lấy email từ Google');
            $this->redirect('/login');
            return;
        }
        
        $userModel = new User();
        
        // Check if user exists by google_id
        $user = $userModel->findByGoogleId($userInfo['id']);
        
        if (!$user) {
            // Check if email exists
            $user = $userModel->findByEmail($userInfo['email']);
            
            if ($user) {
                // Update google_id
                $userModel->update($user['id'], ['google_id' => $userInfo['id']]);
            } else {
                // Create new user
                $username = explode('@', $userInfo['email'])[0] . rand(100, 999);
                $userId = $userModel->createUser([
                    'name' => $userInfo['name'] ?? $userInfo['email'],
                    'username' => $username,
                    'email' => $userInfo['email'],
                    'google_id' => $userInfo['id'],
                    'avatar' => $userInfo['picture'] ?? null,
                    'role' => 'user',
                    'status' => 'active',
                    'email_verified_at' => date('Y-m-d H:i:s')
                ]);
                
                $user = $userModel->find($userId);
            }
        }
        
        // Login user
        Auth::login($user);
        $this->redirect('/user/dashboard');
    }
}
