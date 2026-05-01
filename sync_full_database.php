<?php

define('ROOT_PATH', __DIR__);

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

function connectDatabase(): PDO
{
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? '';
    $username = $_ENV['DB_USER'] ?? '';
    $password = $_ENV['DB_PASS'] ?? '';

    if ($dbname === '' || $username === '') {
        throw new RuntimeException('Missing DB configuration in .env');
    }

    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    return $pdo;
}

function logLine(string $message): void
{
    echo $message . PHP_EOL;
}

function shouldIgnoreSqlError(PDOException $e): bool
{
    $message = $e->getMessage();
    $ignorablePatterns = [
        'already exists',
        'Duplicate entry',
        'Duplicate column name',
        'Duplicate key name',
        'Duplicate foreign key constraint name',
        'errno: 121',
        '1005',
        '1060',
        '1061',
        '1050',
        '1062',
        '1091',
        '1826',
    ];

    foreach ($ignorablePatterns as $pattern) {
        if (stripos($message, $pattern) !== false) {
            return true;
        }
    }

    return false;
}

function executeStatements(PDO $pdo, string $label, string $sql): void
{
    $sql = preg_replace('/^\s*CREATE\s+DATABASE\b.*?;\s*$/mi', '', $sql);
    $sql = preg_replace('/^\s*USE\s+`?.+?`?\s*;\s*$/mi', '', $sql);
    $sql = preg_replace('/^\s*START TRANSACTION\s*;\s*$/mi', '', $sql);
    $sql = preg_replace('/^\s*COMMIT\s*;\s*$/mi', '', $sql);

    // Make base imports re-runnable.
    $sql = preg_replace('/CREATE TABLE\s+`/i', 'CREATE TABLE IF NOT EXISTS `', $sql);
    $sql = preg_replace('/INSERT INTO\s+`/i', 'INSERT IGNORE INTO `', $sql);
    $sql = preg_replace('/INSERT INTO\s+([a-zA-Z_][a-zA-Z0-9_]*)/i', 'INSERT IGNORE INTO $1', $sql);

    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $ok = 0;
    $ignored = 0;

    logLine("== {$label} ==");

    foreach ($statements as $statement) {
        if ($statement === '') {
            continue;
        }

        try {
            $pdo->exec($statement);
            $ok++;
        } catch (PDOException $e) {
            if (shouldIgnoreSqlError($e)) {
                $ignored++;
                continue;
            }

            throw $e;
        }
    }

    logLine("Applied: {$ok}, ignored: {$ignored}");
}

function executeSqlFile(PDO $pdo, string $relativePath, string $label): void
{
    $path = ROOT_PATH . '/' . $relativePath;
    if (!file_exists($path)) {
        logLine("Skip {$label}: file not found");
        return;
    }

    executeStatements($pdo, $label, file_get_contents($path));
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare("
        SELECT 1
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
        LIMIT 1
    ");
    $stmt->execute([$table]);

    return (bool) $stmt->fetchColumn();
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    if (!tableExists($pdo, $table)) {
        return false;
    }

    $stmt = $pdo->prepare("
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
        LIMIT 1
    ");
    $stmt->execute([$table, $column]);

    return (bool) $stmt->fetchColumn();
}

function ensureColumn(PDO $pdo, string $table, string $column, string $definition): void
{
    if (!columnExists($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        logLine("Added column {$table}.{$column}");
    }
}

function ensureMenus(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS menus (
          id int(11) NOT NULL AUTO_INCREMENT,
          title varchar(255) NOT NULL,
          url varchar(255) DEFAULT '#',
          icon varchar(100) DEFAULT NULL,
          parent_id int(11) DEFAULT NULL,
          display_order int(11) DEFAULT 0,
          status enum('active','inactive') DEFAULT 'active',
          PRIMARY KEY (id),
          FOREIGN KEY (parent_id) REFERENCES menus(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $count = (int) $pdo->query("SELECT COUNT(*) FROM menus")->fetchColumn();
    if ($count > 0) {
        logLine('Menus already seeded');
        return;
    }

    $menus = [
        [1, 'San pham', '#', null, null, 1, 'active'],
        [2, 'Dich vu', '#', null, null, 2, 'active'],
        [3, 'Ho tro', '/support', null, null, 3, 'active'],
        [4, 'Cong cu', '#', null, null, 4, 'active'],
        [5, 'FAQs', '/faqs', null, null, 5, 'active'],
        [null, 'Email Marketing', '/category/email-marketing', 'fas fa-envelope', 1, 1, 'active'],
        [null, 'Phan mem', '/category/phan-mem', 'fas fa-laptop-code', 1, 2, 'active'],
        [null, 'Tang tuong tac', '/category/tang-tuong-tac', 'fas fa-chart-line', 2, 1, 'active'],
        [null, 'Blockchain', '/category/blockchain', 'fab fa-bitcoin', 2, 2, 'active'],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO menus (id, title, url, icon, parent_id, display_order, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($menus as $menu) {
        $stmt->execute($menu);
    }

    logLine('Seeded menus');
}

function ensureExtraTables(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT(11) NOT NULL AUTO_INCREMENT,
            user_id INT(11) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) NOT NULL DEFAULT 'info',
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY is_read (is_read),
            CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_favorites (
            id INT(11) NOT NULL AUTO_INCREMENT,
            user_id INT(11) NOT NULL,
            product_id INT(11) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_product_unique (user_id, product_id),
            KEY user_id (user_id),
            KEY product_id (product_id),
            CONSTRAINT fk_user_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_user_favorites_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS seller_deactivation_requests (
            id INT(11) NOT NULL AUTO_INCREMENT,
            seller_id INT(11) NOT NULL,
            request_date DATETIME NOT NULL,
            hold_until DATETIME NOT NULL,
            refund_amount DECIMAL(15,2) NOT NULL,
            status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
            reason TEXT,
            admin_note TEXT,
            processed_at DATETIME DEFAULT NULL,
            processed_by INT(11) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY seller_id (seller_id),
            KEY status (status),
            KEY hold_until (hold_until)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS spam_alerts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    logLine('Ensured extra tables');
}

function ensureSystemSettings(PDO $pdo): void
{
    if (!tableExists($pdo, 'system_settings')) {
        logLine('Skip system settings seed: system_settings table missing');
        return;
    }

    $pdo->exec("
        INSERT INTO system_settings (setting_key, setting_value, description) VALUES
        ('seller_minimum_balance', '500000', 'Minimum seller balance in VND'),
        ('seller_deactivation_hold_days', '7', 'Days to wait before seller deactivation is processed'),
        ('deposit_percentage', '30', 'Default seller stock deposit percentage'),
        ('dispute_seller_response_hours', '24', 'Seller response SLA for active disputes'),
        ('dispute_admin_resolution_hours', '48', 'Admin resolution SLA after seller response window')
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");

    logLine('Seeded system_settings');
}

function ensureAppSettings(PDO $pdo): void
{
    if (!tableExists($pdo, 'settings')) {
        logLine('Skip app settings seed: settings table missing');
        return;
    }

    $pdo->exec("
        INSERT INTO settings (key_name, value) VALUES
        ('deposit_bank_code', 'mb'),
        ('deposit_bank_name', 'MB Bank'),
        ('deposit_account_name', 'TRAN THANH TUAN'),
        ('deposit_account_number', '0783704196')
        ON DUPLICATE KEY UPDATE value = VALUES(value)
    ");

    logLine('Seeded settings');
}

loadEnv(ROOT_PATH . '/.env');

try {
    logLine('=== Full database sync started ===');
    $pdo = connectDatabase();
    logLine('Connected to database: ' . ($_ENV['DB_NAME'] ?? ''));

    executeSqlFile($pdo, 'database.sql', 'Base schema');
    executeSqlFile($pdo, 'database_chat_update.sql', 'Chat schema');
    executeSqlFile($pdo, 'database_escrow_system.sql', 'Escrow schema');
    executeSqlFile($pdo, 'migrations/dispute_system.sql', 'Dispute schema');
    executeSqlFile($pdo, 'migrations/business_rules_update.sql', 'Business rules');
    executeSqlFile($pdo, 'migrations/articles.sql', 'Articles schema');

    ensureColumn($pdo, 'users', 'last_active_at', 'DATETIME DEFAULT NULL AFTER `updated_at`');
    ensureColumn($pdo, 'users', 'is_seller_requested', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`');
    ensureColumn($pdo, 'users', 'max_products', 'INT(11) NOT NULL DEFAULT 10 AFTER `is_seller_requested`');
    ensureColumn($pdo, 'order_items', 'is_read', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `seller_amount`');
    ensureMenus($pdo);
    ensureExtraTables($pdo);
    ensureSystemSettings($pdo);
    ensureAppSettings($pdo);

    logLine('=== Full database sync completed ===');
    logLine('Run command: php sync_full_database.php');
} catch (Throwable $e) {
    logLine('Sync failed: ' . $e->getMessage());
    exit(1);
}
