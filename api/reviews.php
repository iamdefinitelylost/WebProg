<?php

declare(strict_types=1);

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../db.php';

function review_response(array $data, int $status = 200): never
{
    api_json($data, $status);
}

function review_json_body(): array
{
    $data = json_decode(file_get_contents('php://input') ?: '', true);
    return is_array($data) ? $data : [];
}

function require_admin_review_access(): void
{
    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        review_response(['success' => false, 'error' => 'Administrator login required.'], 401);
    }
}

function require_customer_review_access(): void
{
    if (($_SESSION['user']['role'] ?? '') !== 'customer') {
        review_response(['success' => false, 'error' => 'Customer login required.'], 401);
    }
}

function ensure_reviews_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        rating TINYINT NOT NULL,
        comment VARCHAR(500) NOT NULL,
        approved TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $check = $pdo->prepare(
        "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME = 'reviews'
         AND COLUMN_NAME = 'approved' LIMIT 1"
    );
    $check->execute();

    if (!$check->fetchColumn()) {
        $pdo->exec('ALTER TABLE reviews ADD COLUMN approved TINYINT(1) NOT NULL DEFAULT 1');
    }
}

ensure_reviews_schema($pdo);
$action = strtolower(trim((string)($_GET['action'] ?? '')));

try {
    if ($action === 'admin_list') {
        require_admin_review_access();

        $stmt = $pdo->query(
            'SELECT r.id, r.rating, r.comment, r.approved, r.created_at,
                    u.id AS user_id, u.name, u.email, u.avatar_path,
                    p.id AS product_id, p.name AS product_name
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             JOIN products p ON p.id = r.product_id
             ORDER BY r.created_at DESC'
        );

        review_response(['success' => true, 'reviews' => $stmt->fetchAll()]);
    }

    if ($action === 'admin_update') {
        require_admin_review_access();
        require_csrf();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            review_response(['success' => false, 'error' => 'Method not allowed.'], 405);
        }

        $data = review_json_body();
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        $approved = !empty($data['approved']) ? 1 : 0;

        if (!$id) {
            review_response(['success' => false, 'error' => 'Review id is required.'], 422);
        }

        $stmt = $pdo->prepare('UPDATE reviews SET approved = :approved WHERE id = :id');
        $stmt->execute([':approved' => $approved, ':id' => $id]);

        review_response(['success' => true]);
    }

    if ($action === 'admin_delete') {
        require_admin_review_access();
        require_csrf();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            review_response(['success' => false, 'error' => 'Method not allowed.'], 405);
        }

        $data = review_json_body();
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {
            review_response(['success' => false, 'error' => 'Review id is required.'], 422);
        }

        $stmt = $pdo->prepare('DELETE FROM reviews WHERE id = :id');
        $stmt->execute([':id' => $id]);

        review_response(['success' => true]);
    }

    $productId = filter_var($_GET['product_id'] ?? null, FILTER_VALIDATE_INT);

    if (!$productId) {
        review_response(['success' => false, 'error' => 'Product required.'], 422);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare(
            'SELECT r.id, r.rating, r.comment, r.created_at, u.name, u.avatar_path
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.product_id = :product_id AND r.approved = 1
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([':product_id' => $productId]);
        $reviews = $stmt->fetchAll();

        $average = $reviews
            ? array_sum(array_map(static fn(array $row): int => (int)$row['rating'], $reviews)) / count($reviews)
            : 0;

        review_response([
            'success' => true,
            'reviews' => $reviews,
            'average' => round($average, 1),
            'count' => count($reviews),
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        review_response(['success' => false, 'error' => 'Method not allowed.'], 405);
    }

    require_customer_review_access();
    require_csrf();

    $data = review_json_body();
    $rating = filter_var($data['rating'] ?? null, FILTER_VALIDATE_INT);
    $comment = trim((string)($data['comment'] ?? ''));

    // A 0-star review is valid; FILTER_VALIDATE_INT returns 0 correctly.
    if ($rating === false || $rating < 0 || $rating > 5) {
        review_response(['success' => false, 'error' => 'Rating must be between 0 and 5.'], 422);
    }

    if ($comment === '' || strlen($comment) > 500) {
        review_response(['success' => false, 'error' => 'Review must contain 1–500 characters.'], 422);
    }

    $userId = (int)$_SESSION['user']['id'];

    $existing = $pdo->prepare(
        'SELECT id FROM reviews WHERE user_id = :user_id AND product_id = :product_id LIMIT 1'
    );
    $existing->execute([':user_id' => $userId, ':product_id' => $productId]);

    if ($existing->fetch()) {
        $update = $pdo->prepare(
            'UPDATE reviews
             SET rating = :rating, comment = :comment, approved = 1
             WHERE user_id = :user_id AND product_id = :product_id'
        );
        $update->execute([
            ':rating' => $rating,
            ':comment' => $comment,
            ':user_id' => $userId,
            ':product_id' => $productId,
        ]);
    } else {
        $insert = $pdo->prepare(
            'INSERT INTO reviews (user_id, product_id, rating, comment, approved)
             VALUES (:user_id, :product_id, :rating, :comment, 1)'
        );
        $insert->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
            ':rating' => $rating,
            ':comment' => $comment,
        ]);
    }

    review_response(['success' => true]);
} catch (Throwable $e) {
    error_log('[F1 MERCH reviews.php] ' . $e->getMessage());
    review_response(['success' => false, 'error' => 'Unable to process the review request.'], 500);
}
