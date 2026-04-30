<?php
/**
 * MMO Marketplace - Auto Installer
 * Upload file này vào public_html/ và truy cập: https://yourdomain.com/install.php
 * XÓA FILE NÀY SAU KHI CÀI ĐẶT XONG!
 */

// Ngăn chặn truy cập nếu đã cài đặt
if (file_exists(__DIR__ . '/.installed')) {
    die('
    <html>
    <head>
        <title>Already Installed</title>
        <style>
            body { font-family: Arial; padding: 50px; text-align: center; }
            .error { color: red; font-size: 20px; }
        </style>
    </head>
    <body>
        <h1 class="error">⚠️ Website đã được cài đặt!</h1>
        <p>Vui lòng xóa file install.php để bảo mật.</p>
        <p><a href="/">← Về trang chủ</a></p>
    </body>
    </html>
    ');
}

// Load .env
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/.env';
}

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, '"\'');
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? '';
$username = $_ENV['DB_USER'] ?? '';
$password = $_ENV['DB_PASS'] ?? '';

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMO Marketplace - Installation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .step {
            background: #f8f9fa;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        .step h3 {
            color: #28a745;
            margin-bottom: 10px;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #0066cc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #218838;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        ul {
            margin: 15px 0;
            padding-left: 30px;
        }
        li {
            margin: 8px 0;
        }
        .progress {
            background: #f0f0f0;
            height: 30px;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-bar {
            background: linear-gradient(90deg, #28a745, #20c997);
            height: 100%;
            line-height: 30px;
            color: white;
            text-align: center;
            font-weight: bold;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 MMO Marketplace</h1>
            <p>Hệ thống cài đặt tự động</p>
        </div>
        
        <div class="content">
            <?php
            $errors = [];
            $success = true;
            
            // Kiểm tra thông tin database
            if (empty($dbname) || empty($username)) {
                $errors[] = "Thông tin database trong file .env chưa đầy đủ!";
                $success = false;
            }
            
            if (!$success) {
                echo '<div class="step">';
                echo '<h3 class="error">❌ Lỗi cấu hình</h3>';
                foreach ($errors as $error) {
                    echo "<p class='error'>• $error</p>";
                }
                echo '<div class="info">';
                echo '<strong>Hướng dẫn:</strong><br>';
                echo '1. Mở file .env<br>';
                echo '2. Cập nhật thông tin database:<br>';
                echo '<code>DB_HOST=localhost<br>';
                echo 'DB_NAME=your_database_name<br>';
                echo 'DB_USER=your_database_user<br>';
                echo 'DB_PASS=your_database_password</code><br>';
                echo '3. Tải lại trang này';
                echo '</div>';
                echo '</div>';
            } else {
                try {
                    // Bước 1: Kết nối database
                    echo '<div class="step">';
                    echo '<h3>📡 Bước 1: Kết nối Database</h3>';
                    
                    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    echo "<p class='success'>✅ Kết nối thành công!</p>";
                    echo "<p>Host: <strong>$host</strong></p>";
                    echo "<p>User: <strong>$username</strong></p>";
                    echo '</div>';
                    
                    // Bước 2: Tạo database
                    echo '<div class="step">';
                    echo '<h3>🗄️ Bước 2: Tạo Database</h3>';
                    
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdo->exec("USE `$dbname`");
                    
                    echo "<p class='success'>✅ Database '$dbname' đã sẵn sàng!</p>";
                    echo '</div>';
                    
                    // Bước 3: Import SQL
                    echo '<div class="step">';
                    echo '<h3>📥 Bước 3: Import Database</h3>';
                    
                    $sqlFile = __DIR__ . '/../database.sql';
                    if (!file_exists($sqlFile)) {
                        throw new Exception("Không tìm thấy file database.sql");
                    }
                    
                    $sql = file_get_contents($sqlFile);
                    
                    // Xóa comments và USE database
                    $sql = preg_replace('/^--.*$/m', '', $sql);
                    $sql = preg_replace('/^\/\*.*?\*\//ms', '', $sql);
                    $sql = preg_replace('/USE `.*?`;/i', '', $sql);
                    $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
                    
                    // Tách câu lệnh
                    $statements = array_filter(array_map('trim', explode(';', $sql)));
                    
                    $total = count($statements);
                    $executed = 0;
                    $failed = 0;
                    
                    echo "<div class='progress'>";
                    echo "<div class='progress-bar' style='width: 0%'>0%</div>";
                    echo "</div>";
                    echo "<p>Đang import <strong>$total</strong> câu lệnh SQL...</p>";
                    
                    foreach ($statements as $index => $statement) {
                        if (!empty($statement)) {
                            try {
                                $pdo->exec($statement);
                                $executed++;
                            } catch (PDOException $e) {
                                // Bỏ qua lỗi table exists
                                if (strpos($e->getMessage(), 'already exists') === false && 
                                    strpos($e->getMessage(), 'Duplicate') === false) {
                                    $failed++;
                                }
                            }
                        }
                        
                        // Update progress
                        if ($index % 10 == 0) {
                            $percent = round(($index / $total) * 100);
                            echo "<script>document.querySelector('.progress-bar').style.width = '{$percent}%'; document.querySelector('.progress-bar').textContent = '{$percent}%';</script>";
                            flush();
                        }
                    }
                    
                    echo "<script>document.querySelector('.progress-bar').style.width = '100%'; document.querySelector('.progress-bar').textContent = '100%';</script>";
                    
                    echo "<p class='success'>✅ Đã thực thi: <strong>$executed</strong> câu lệnh</p>";
                    if ($failed > 0) {
                        echo "<p class='warning'>⚠️ Bỏ qua: <strong>$failed</strong> lỗi (có thể do table đã tồn tại)</p>";
                    }
                    echo '</div>';
                    
                    // Bước 4: Kiểm tra tables
                    echo '<div class="step">';
                    echo '<h3>📊 Bước 4: Kiểm tra Tables</h3>';
                    
                    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                    
                    echo "<p class='success'>✅ Đã tạo <strong>" . count($tables) . "</strong> bảng:</p>";
                    echo "<table>";
                    echo "<tr><th>Tên bảng</th><th>Số records</th></tr>";
                    
                    foreach ($tables as $table) {
                        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                        echo "<tr><td><strong>$table</strong></td><td>$count</td></tr>";
                    }
                    
                    echo "</table>";
                    echo '</div>';
                    
                    // Bước 5: Tài khoản demo
                    echo '<div class="step">';
                    echo '<h3>👥 Bước 5: Tài khoản Demo</h3>';
                    
                    $users = $pdo->query("SELECT id, name, email, role FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo "<table>";
                    echo "<tr><th>Email</th><th>Password</th><th>Role</th></tr>";
                    
                    foreach ($users as $user) {
                        $roleColor = [
                            'admin' => '#dc3545',
                            'seller' => '#28a745',
                            'user' => '#0066cc',
                            'affiliate' => '#ffc107'
                        ];
                        $color = $roleColor[$user['role']] ?? '#666';
                        
                        echo "<tr>";
                        echo "<td><strong>{$user['email']}</strong></td>";
                        echo "<td><code>123456</code></td>";
                        echo "<td><span style='color: $color; font-weight: bold;'>{$user['role']}</span></td>";
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                    echo '</div>';
                    
                    // Tạo file .installed
                    file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));
                    
                    // Thành công
                    echo '<div class="step" style="background: #d4edda; border-color: #28a745;">';
                    echo '<h2 style="color: #28a745;">🎉 CÀI ĐẶT HOÀN TẤT!</h2>';
                    echo '<div class="info" style="background: #fff3cd; border-color: #ffc107;">';
                    echo '<h3 style="color: #856404;">⚠️ QUAN TRỌNG - Làm ngay:</h3>';
                    echo '<ol>';
                    echo '<li><strong style="color: red;">XÓA FILE install.php NGAY LẬP TỨC!</strong></li>';
                    echo '<li>Đăng nhập admin và đổi mật khẩu</li>';
                    echo '<li>Xóa các tài khoản demo không cần thiết</li>';
                    echo '<li>Cập nhật thông tin website trong Admin → Settings</li>';
                    echo '</ol>';
                    echo '</div>';
                    
                    echo '<h3>🚀 Bước tiếp theo:</h3>';
                    echo '<p><a href="/" class="btn">🏠 Về trang chủ</a></p>';
                    echo '<p><a href="/login" class="btn">🔐 Đăng nhập Admin</a></p>';
                    echo '<p><a href="?delete=1" class="btn btn-danger" onclick="return confirm(\'Xác nhận xóa file install.php?\')">🗑️ Xóa file install.php</a></p>';
                    echo '</div>';
                    
                } catch (PDOException $e) {
                    echo '<div class="step">';
                    echo '<h3 class="error">❌ Lỗi Database</h3>';
                    echo "<p class='error'>" . $e->getMessage() . "</p>";
                    echo '<div class="info">';
                    echo '<strong>Kiểm tra:</strong><br>';
                    echo '• Thông tin database trong .env có đúng không?<br>';
                    echo '• Database user có quyền CREATE DATABASE không?<br>';
                    echo '• MySQL server có đang chạy không?<br>';
                    echo '• File database.sql có tồn tại không?';
                    echo '</div>';
                    echo '</div>';
                } catch (Exception $e) {
                    echo '<div class="step">';
                    echo '<h3 class="error">❌ Lỗi</h3>';
                    echo "<p class='error'>" . $e->getMessage() . "</p>";
                    echo '</div>';
                }
            }
            
            // Xử lý xóa file
            if (isset($_GET['delete']) && $_GET['delete'] == '1') {
                if (unlink(__FILE__)) {
                    echo '<script>alert("✅ Đã xóa file install.php thành công!"); window.location.href = "/";</script>';
                } else {
                    echo '<script>alert("❌ Không thể xóa file. Vui lòng xóa thủ công qua FTP!");</script>';
                }
            }
            ?>
        </div>
    </div>
</body>
</html>
