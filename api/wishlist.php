<?php

declare(strict_types=1);

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../db.php';

function wishlist_response(array $data, int $status = 200): never
{
    api_json($data, $status);
}

function require_customer(): void
{
    if (($_SESSION['user']['role'] ?? '') !== 'customer') {
        wishlist_response(['success' => false, 'error' => 'Customer login required.'], 401);
    }
}

try {
    require_customer();

    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlists (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_wish(user_id, product_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $action = strtolower(trim((string)($_GET['action'] ?? 'list')));
    $userId = (int)$_SESSION['user']['id'];

    if ($action === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare(
            'SELECT product_id FROM wishlists WHERE user_id = :user_id ORDER BY created_at DESC'
        );
        $stmt->execute([':user_id' => $userId]);

        $items = array_map(
            static fn(array $row): int => (int)$row['product_id'],
            $stmt->fetchAll()
        );

        wishlist_response(['success' => true, 'items' => $items]);
    }

    if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf();

        $data = json_decode(file_get_contents('php://input') ?: '', true);
        $productId = filter_var($data['product_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$productId) {
            wishlist_response(['success' => false, 'error' => 'Product required.'], 422);
        }

        $product = $pdo->prepare('SELECT id FROM products WHERE id = :id');
        $product->execute([':id' => $productId]);
        if (!$product->fetch()) {
            wishlist_response(['success' => false, 'error' => 'Product not found.'], 404);
        }

        $find = $pdo->prepare(
            'SELECT id FROM wishlists WHERE user_id = :user_id AND product_id = :product_id'
        );
        $find->execute([':user_id' => $userId, ':product_id' => $productId]);

        if ($find->fetch()) {
            $delete = $pdo->prepare(
                'DELETE FROM wishlists WHERE user_id = :user_id AND product_id = :product_id'
            );
            $delete->execute([':user_id' => $userId, ':product_id' => $productId]);
            wishlist_response(['success' => true, 'wishlisted' => false]);
        }

        $insert = $pdo->prepare(
            'INSERT INTO wishlists (user_id, product_id) VALUES (:user_id, :product_id)'
        );
        $insert->execute([':user_id' => $userId, ':product_id' => $productId]);

        wishlist_response(['success' => true, 'wishlisted' => true]);
    }

    wishlist_response(['success' => false, 'error' => 'Unknown wishlist action.'], 400);
} catch (Throwable $e) {
    error_log('[F1 MERCH wishlist.php] ' . $e->getMessage());
    wishlist_response(['success' => false, 'error' => 'Unable to process wishlist request.'], 500);
}
