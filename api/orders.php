<?php

declare(strict_types=1);

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../db.php';

const ORDER_STATUSES = [
    'pending',
    'processing',
    'shipped',
    'out_for_delivery',
    'delivered',
    'cancelled',
];

const SHIPPING_PRICES = [
    'standard' => 0.0,
    'express' => 450.0,
];

function order_response(array $data, int $status = 200): never
{
    api_json($data, $status);
}

function ensure_orders_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_number VARCHAR(40) NOT NULL UNIQUE,
        item_summary VARCHAR(255) NOT NULL,
        total DECIMAL(10,2) NOT NULL DEFAULT 0,
        status ENUM('pending','processing','shipped','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $columns = [
        'customer_name' => 'VARCHAR(100) NULL',
        'phone' => 'VARCHAR(30) NULL',
        'address' => 'VARCHAR(180) NULL',
        'city' => 'VARCHAR(80) NULL',
        'province' => 'VARCHAR(80) NULL',
        'postal_code' => 'VARCHAR(10) NULL',
        'shipping_method' => 'VARCHAR(20) NULL',
        'payment_method' => 'VARCHAR(30) NULL',
    ];

    foreach ($columns as $column => $definition) {
        $check = $pdo->prepare(
            "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'orders'
             AND COLUMN_NAME = :column LIMIT 1"
        );
        $check->execute([':column' => $column]);

        if (!$check->fetchColumn()) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN `$column` $definition");
        }
    }
}

function require_admin_order_access(): void
{
    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        order_response(['success' => false, 'error' => 'Administrator login required.'], 401);
    }
}

function request_json(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);
    return is_array($data) ? $data : [];
}

ensure_orders_schema($pdo);

$action = strtolower(trim((string)($_GET['action'] ?? 'my')));
$role = strtolower(trim((string)($_SESSION['user']['role'] ?? '')));

try {
    switch ($action) {
        case 'admin_list':
            require_admin_order_access();

            $stmt = $pdo->query(
                'SELECT o.*, u.email
                 FROM orders o
                 JOIN users u ON u.id = o.user_id
                 ORDER BY o.created_at DESC'
            );

            order_response(['success' => true, 'orders' => $stmt->fetchAll()]);

        case 'admin_update':
            require_admin_order_access();
            require_csrf();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                order_response(['success' => false, 'error' => 'Method not allowed.'], 405);
            }

            $data = request_json();
            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            $status = strtolower(trim((string)($data['status'] ?? '')));

            if (!$id || !in_array($status, ORDER_STATUSES, true)) {
                order_response(['success' => false, 'error' => 'Invalid order update.'], 422);
            }

            $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
            $stmt->execute([':status' => $status, ':id' => $id]);

            if ($stmt->rowCount() === 0) {
                $check = $pdo->prepare('SELECT id FROM orders WHERE id = :id');
                $check->execute([':id' => $id]);
                if (!$check->fetch()) {
                    order_response(['success' => false, 'error' => 'Order not found.'], 404);
                }
            }

            order_response(['success' => true]);

        case 'analytics':
            require_admin_order_access();

            $stats = $pdo->query(
                "SELECT COUNT(*) AS orders,
                        COALESCE(SUM(CASE WHEN status <> 'cancelled' THEN total ELSE 0 END), 0) AS sales,
                        COUNT(DISTINCT user_id) AS customers
                 FROM orders"
            )->fetch();

            $products = $pdo->query(
                'SELECT name, stock, price FROM products ORDER BY stock ASC'
            )->fetchAll();

            $monthly = $pdo->query(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
                        COUNT(*) AS orders,
                        COALESCE(SUM(CASE WHEN status <> 'cancelled' THEN total ELSE 0 END), 0) AS sales
                 FROM orders
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY month DESC LIMIT 12"
            )->fetchAll();

            $status = $pdo->query(
                'SELECT status, COUNT(*) AS count FROM orders GROUP BY status'
            )->fetchAll();

            $topCustomers = $pdo->query(
                "SELECT u.name, u.email, COUNT(o.id) AS orders,
                        COALESCE(SUM(CASE WHEN o.status <> 'cancelled' THEN o.total ELSE 0 END), 0) AS spent
                 FROM orders o
                 JOIN users u ON u.id = o.user_id
                 GROUP BY u.id, u.name, u.email
                 ORDER BY spent DESC LIMIT 10"
            )->fetchAll();

            order_response([
                'success' => true,
                'stats' => $stats,
                'inventory' => $products,
                'monthly' => array_reverse($monthly),
                'status' => $status,
                'top_customers' => $topCustomers,
            ]);

        case 'my':
            if ($role !== 'customer') {
                order_response(['success' => false, 'error' => 'Customer login required.'], 401);
            }

            $stmt = $pdo->prepare(
                'SELECT order_number, item_summary, total, status, created_at, updated_at,
                        customer_name, phone, address, city, province, postal_code,
                        shipping_method, payment_method
                 FROM orders WHERE user_id = :user_id ORDER BY created_at DESC'
            );
            $stmt->execute([':user_id' => (int)$_SESSION['user']['id']]);

            order_response(['success' => true, 'orders' => $stmt->fetchAll()]);

        case 'create':
            if ($role !== 'customer') {
                order_response(['success' => false, 'error' => 'Customer login required.'], 401);
            }
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                order_response(['success' => false, 'error' => 'Method not allowed.'], 405);
            }
            require_csrf();

            $data = request_json();
            $required = [
                'full_name', 'phone', 'address', 'city', 'province',
                'postal_code', 'shipping_method', 'payment_method', 'items'
            ];

            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '' || ($field === 'items' && !is_array($data[$field]))) {
                    order_response(['success' => false, 'error' => 'Please complete all checkout fields.'], 400);
                }
            }

            $shipping = (string)$data['shipping_method'];
            $payment = (string)$data['payment_method'];

            if (!isset(SHIPPING_PRICES[$shipping]) || !in_array($payment, ['cod', 'gcash'], true)) {
                order_response(['success' => false, 'error' => 'Invalid checkout selection.'], 422);
            }

            if (count($data['items']) < 1 || count($data['items']) > 50) {
                order_response(['success' => false, 'error' => 'Your cart is empty or too large.'], 400);
            }

            $pdo->beginTransaction();

            try {
                $items = [];
                $subtotal = 0.0;
                $lockProduct = $pdo->prepare(
                    'SELECT id, name, price, stock FROM products WHERE id = :id FOR UPDATE'
                );
                $decreaseStock = $pdo->prepare(
                    'UPDATE products SET stock = stock - :quantity WHERE id = :id'
                );

                foreach ($data['items'] as $item) {
                    $productId = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
                    $quantity = filter_var($item['qty'] ?? null, FILTER_VALIDATE_INT);

                    if (!$productId || !$quantity || $quantity < 1 || $quantity > 99) {
                        throw new InvalidArgumentException('Invalid item in cart.');
                    }

                    $lockProduct->execute([':id' => $productId]);
                    $product = $lockProduct->fetch();

                    if (!$product) {
                        throw new RuntimeException('A product in your cart is no longer available.');
                    }

                    if ((int)$product['stock'] < $quantity) {
                        throw new RuntimeException(
                            $product['name'] . ' only has ' . $product['stock'] . ' left in stock.'
                        );
                    }

                    $price = (float)$product['price'];
                    $subtotal += $price * $quantity;
                    $items[] = [
                        'name' => $product['name'],
                        'qty' => $quantity,
                        'price' => $price,
                    ];

                    $decreaseStock->execute([
                        ':quantity' => $quantity,
                        ':id' => $productId,
                    ]);
                }

                $shippingCost = SHIPPING_PRICES[$shipping];
                $total = $subtotal + $shippingCost;
                $summary = implode(', ', array_map(
                    static fn(array $item): string => $item['name'] . ' × ' . $item['qty'],
                    $items
                ));
                $summary = strlen($summary) > 255 ? substr($summary, 0, 252) . '...' : $summary;
                $orderNumber = 'F1-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

                $insert = $pdo->prepare(
                    "INSERT INTO orders
                    (user_id, order_number, item_summary, total, status, customer_name, phone,
                     address, city, province, postal_code, shipping_method, payment_method)
                    VALUES (:user_id, :order_number, :summary, :total, 'pending', :name, :phone,
                            :address, :city, :province, :postal, :shipping, :payment)"
                );
                $insert->execute([
                    ':user_id' => (int)$_SESSION['user']['id'],
                    ':order_number' => $orderNumber,
                    ':summary' => $summary,
                    ':total' => $total,
                    ':name' => trim((string)$data['full_name']),
                    ':phone' => trim((string)$data['phone']),
                    ':address' => trim((string)$data['address']),
                    ':city' => trim((string)$data['city']),
                    ':province' => trim((string)$data['province']),
                    ':postal' => trim((string)$data['postal_code']),
                    ':shipping' => $shipping,
                    ':payment' => $payment,
                ]);

                $pdo->commit();

                order_response([
                    'success' => true,
                    'order' => [
                        'order_number' => $orderNumber,
                        'total' => $total,
                        'status' => 'pending',
                    ],
                ]);
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                throw $e;
            }

        default:
            order_response(['success' => false, 'error' => 'Unknown order action.'], 400);
    }
} catch (Throwable $e) {
    error_log('[F1 MERCH orders.php] ' . $e->getMessage());
    order_response(['success' => false, 'error' => 'Unable to process the order request.'], 500);
}
