<?php
// F1 MERCH - database connection + automatic compatibility migration.
// XAMPP defaults: host=localhost, database=f1_merch, user=root, blank password.

$DB_HOST = 'localhost';
$DB_NAME = 'f1_merch';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // ------------------------------------------------------------
    // Compatibility migration
    // ------------------------------------------------------------
    // This project has evolved over several versions. Existing XAMPP
    // databases may already contain users/products but be missing newer
    // columns. Add only missing columns so login, banning, profiles and
    // product photos work without requiring the user to delete/re-import
    // their existing database.
    function f1_column_exists(PDO $pdo, string $table, string $column): bool {
        $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name LIMIT 1");
        $stmt->execute([':table_name' => $table, ':column_name' => $column]);
        return (bool)$stmt->fetch();
    }

    function f1_table_exists(PDO $pdo, string $table): bool {
        $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name LIMIT 1");
        $stmt->execute([':table_name' => $table]);
        return (bool)$stmt->fetch();
    }

    // Users table (for older databases that predate authentication).
    if (!f1_table_exists($pdo, 'users')) {
        $pdo->exec("CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
            banned_at TIMESTAMP NULL DEFAULT NULL,
            avatar_path VARCHAR(255) NULL,
            bio VARCHAR(240) NULL,
            favorite_team VARCHAR(80) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } else {
        $userColumns = [
            'banned_at' => "ALTER TABLE users ADD COLUMN banned_at TIMESTAMP NULL DEFAULT NULL",
            'avatar_path' => "ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL",
            'bio' => "ALTER TABLE users ADD COLUMN bio VARCHAR(240) NULL",
            'favorite_team' => "ALTER TABLE users ADD COLUMN favorite_team VARCHAR(80) NULL",
        ];
        foreach ($userColumns as $column => $sql) {
            if (!f1_column_exists($pdo, 'users', $column)) {
                $pdo->exec($sql);
            }
        }
    }

    // Products table may predate product-photo support.
    if (f1_table_exists($pdo, 'products') && !f1_column_exists($pdo, 'products', 'image_path')) {
        $pdo->exec("ALTER TABLE products ADD COLUMN image_path VARCHAR(255) NULL");
    }

    // Orders table for customer package tracking.
    if (!f1_table_exists($pdo, 'orders')) {
        $pdo->exec("CREATE TABLE orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            order_number VARCHAR(40) NOT NULL UNIQUE,
            item_summary VARCHAR(255) NOT NULL,
            total DECIMAL(10,2) NOT NULL DEFAULT 0,
            status ENUM('pending','processing','shipped','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

} catch (Throwable $e) {
    throw new RuntimeException('Database connection/migration failed: ' . $e->getMessage(), 0, $e);
}
