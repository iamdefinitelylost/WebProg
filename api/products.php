<?php

declare(strict_types=1);

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../db.php';

const PRODUCT_CATEGORIES = [
    'headwear',
    'outerwear',
    'tees',
    'archive',
];

function product_response(array $data, int $status = 200): never
{
    api_json($data, $status);
}

function require_product_admin(): void
{
    if (
        empty($_SESSION['user'])
        || ($_SESSION['user']['role'] ?? '') !== 'admin'
        || empty($_SESSION['admin_authenticated'])
    ) {
        product_response([
            'success' => false,
            'error' => 'Administrator login required.',
        ], 401);
    }
}

function ensure_product_schema(PDO $pdo): void
{
    if (!table_exists($pdo, 'products')) {
        product_response([
            'success' => false,
            'error' => 'Products table is missing. Import schema.sql first.',
        ], 500);
    }

    $columns = [
        'image_path' => 'VARCHAR(255) NULL',
        'stock' => 'INT NOT NULL DEFAULT 25',
        'team' => 'VARCHAR(80) NULL',
        'sizes' => 'VARCHAR(120) NULL',
        'limited_edition' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'featured' => 'TINYINT(1) NOT NULL DEFAULT 0',
    ];

    foreach ($columns as $column => $definition) {
        $stmt = $pdo->prepare(
            "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'products'
             AND COLUMN_NAME = :column LIMIT 1"
        );
        $stmt->execute([':column' => $column]);

        if (!$stmt->fetchColumn()) {
            $pdo->exec("ALTER TABLE products ADD COLUMN `$column` $definition");
        }
    }
}

function table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1 FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1'
    );
    $stmt->execute([':table' => $table]);

    return (bool)$stmt->fetchColumn();
}

function request_data(): array
{
    $contentType = (string)($_SERVER['CONTENT_TYPE'] ?? '');

    if (stripos($contentType, 'multipart/form-data') !== false) {
        return $_POST;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);

    return is_array($data) ? $data : [];
}

function normalize_product(array $row): array
{
    $row['id'] = (int)$row['id'];
    $row['price'] = (float)$row['price'];
    $row['stock'] = (int)$row['stock'];
    $row['limited_edition'] = (bool)$row['limited_edition'];
    $row['featured'] = (bool)$row['featured'];
    $row['sizes'] = $row['sizes']
        ? array_values(array_filter(array_map('trim', explode(',', $row['sizes']))))
        : [];

    return $row;
}

function get_products(PDO $pdo, int $id = 0): never
{
    $sql = 'SELECT id, name, description AS `desc`, price, category,
                   image_path AS image, stock, team, sizes, limited_edition, featured
            FROM products';

    if ($id > 0) {
        $stmt = $pdo->prepare($sql . ' WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            product_response(['success' => false, 'error' => 'Product not found.'], 404);
        }

        product_response(normalize_product($product));
    }

    $stmt = $pdo->query($sql . ' ORDER BY featured DESC, id ASC');
    $products = [];

    foreach ($stmt as $product) {
        $products[] = normalize_product($product);
    }

    product_response($products);
}

function validate_product(array $data): array
{
    $errors = [];
    $name = trim((string)($data['name'] ?? ''));
    $description = trim((string)($data['desc'] ?? ''));
    $category = trim((string)($data['category'] ?? ''));
    $team = trim((string)($data['team'] ?? ''));
    $sizes = $data['sizes'] ?? '';
    $price = $data['price'] ?? null;
    $stock = $data['stock'] ?? 25;

    if ($name === '') {
        $errors[] = 'Name is required.';
    } elseif (strlen($name) > 160) {
        $errors[] = 'Name must be 160 characters or fewer.';
    }

    if ($description === '') {
        $errors[] = 'Description is required.';
    } elseif (strlen($description) > 2000) {
        $errors[] = 'Description must be 2,000 characters or fewer.';
    }

    if ($price === null || !is_numeric($price) || (float)$price < 0 || (float)$price > 99999999.99) {
        $errors[] = 'Valid price is required.';
    }

    if (!in_array($category, PRODUCT_CATEGORIES, true)) {
        $errors[] = 'Valid category is required.';
    }

    if (filter_var($stock, FILTER_VALIDATE_INT) === false || (int)$stock < 0) {
        $errors[] = 'Stock must be a non-negative whole number.';
    }

    if (strlen($team) > 80) {
        $errors[] = 'Team must be 80 characters or fewer.';
    }

    if (is_array($sizes)) {
        $sizeList = array_values(array_filter(array_map('trim', $sizes)));
    } else {
        $sizeList = array_values(array_filter(array_map('trim', explode(',', (string)$sizes))));
    }

    if (strlen(implode(',', $sizeList)) > 120) {
        $errors[] = 'Sizes must be 120 characters or fewer.';
    }

    if ($errors) {
        product_response(['success' => false, 'errors' => $errors], 422);
    }

    return [
        'name' => $name,
        'desc' => $description,
        'price' => (float)$price,
        'category' => $category,
        'stock' => (int)$stock,
        'team' => $team,
        'sizes' => implode(',', $sizeList),
        'limited' => !empty($data['limited_edition']) ? 1 : 0,
        'featured' => !empty($data['featured']) ? 1 : 0,
    ];
}

function upload_product_image(?array $file): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        product_response(['success' => false, 'error' => 'Product photo upload failed.'], 422);
    }

    if ((int)$file['size'] > 5 * 1024 * 1024) {
        product_response(['success' => false, 'error' => 'Product photo must be 5 MB or smaller.'], 422);
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        product_response(['success' => false, 'error' => 'Invalid uploaded product photo.'], 422);
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        product_response(['success' => false, 'error' => 'Only JPG, PNG, and WebP are allowed.'], 422);
    }

    $directory = __DIR__ . '/../uploads/products';

    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        product_response(['success' => false, 'error' => 'Unable to create product upload folder.'], 500);
    }

    $filename = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    $destination = $directory . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        product_response(['success' => false, 'error' => 'Could not save product photo.'], 500);
    }

    return 'uploads/products/' . $filename;
}

function delete_product_image(?string $path): void
{
    if (!$path) {
        return;
    }

    $file = __DIR__ . '/../' . ltrim($path, '/');

    if (is_file($file)) {
        @unlink($file);
    }
}

function seed_default_products(PDO $pdo): void
{
    $products = [
        ['Pit Lane Cap', 'Six-panel structured cap, embroidered badge, curved brim.', 38, 'headwear', 30, 'Ferrari', 'S,M,L', 0, 1],
        ['Team Snapback', 'Flat-brim snapback, structured crown, mesh rear panel.', 34, 'headwear', 40, 'McLaren', 'S,M,L', 0, 0],
        ['Paddock Hoodie', 'Heavyweight fleece, badge on chest, checker trim on cuff.', 96, 'outerwear', 18, 'Ferrari', 'S,M,L,XL,XXL', 1, 1],
        ['Grid Windbreaker', 'Packable shell, taped seams, badge on left chest.', 118, 'outerwear', 12, 'Mercedes', 'S,M,L,XL', 1, 1],
        ['Race Day Tee', 'Mid-weight cotton, front badge print, back number placeholder.', 32, 'tees', 50, 'Red Bull Racing', 'S,M,L,XL,XXL', 1, 1],
        ['Champion Tee', 'Soft-wash cotton, minimal front hit, checker collar tape.', 30, 'tees', 45, 'McLaren', 'S,M,L,XL,XXL', 0, 0],
        ['Archive Overalls', 'Reissued pit-crew overalls from the archive, twill badge.', 145, 'archive', 8, 'Ferrari', 'S,M,L,XL', 1, 0],
        ['Legacy Varsity Jacket', 'Wool-body varsity jacket, leather sleeves, archive numerals.', 165, 'archive', 6, 'Mercedes', 'S,M,L,XL', 1, 0],
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO products
         (name, description, price, category, stock, team, sizes, limited_edition, featured)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    foreach ($products as $product) {
        $stmt->execute($product);
    }
}

ensure_product_schema($pdo);

$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
$action = strtolower(trim((string)($_GET['action'] ?? '')));

try {
    if ($method === 'GET') {
        get_products($pdo, $id);
    }

    require_product_admin();

    if (!in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
        product_response(['success' => false, 'error' => 'Method not allowed.'], 405);
    }

    require_csrf();

    if ($method === 'POST' && $action === 'reset') {
        $pdo->beginTransaction();

        try {
            $pdo->exec('DELETE FROM products');
            $pdo->exec('ALTER TABLE products AUTO_INCREMENT = 1');
            seed_default_products($pdo);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        get_products($pdo);
    }

    if ($method === 'POST' && $action === 'update') {
        if ($id < 1) {
            product_response([
                'success' => false,
                'error' => 'Missing product id.',
            ], 400);
        }

        $stmt = $pdo->prepare(
            'SELECT image_path FROM products WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            product_response([
                'success' => false,
                'error' => 'Product not found.',
            ], 404);
        }

        // POST + multipart/form-data is used here because PHP reliably
        // populates $_POST and $_FILES for uploaded product images.
        $body = request_data();
        $data = validate_product($body);
        $image = $existing['image_path'];

        if (!empty($body['remove_image'])) {
            delete_product_image($existing['image_path']);
            $image = null;
        }

        $newImage = upload_product_image($_FILES['image'] ?? null);

        if ($newImage !== null) {
            delete_product_image($existing['image_path']);
            $image = $newImage;
        }

        $stmt = $pdo->prepare(
            'UPDATE products
             SET name = :name,
                 description = :description,
                 price = :price,
                 category = :category,
                 image_path = :image,
                 stock = :stock,
                 team = :team,
                 sizes = :sizes,
                 limited_edition = :limited,
                 featured = :featured
             WHERE id = :id'
        );

        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['desc'],
            ':price' => $data['price'],
            ':category' => $data['category'],
            ':image' => $image,
            ':stock' => $data['stock'],
            ':team' => $data['team'],
            ':sizes' => $data['sizes'],
            ':limited' => $data['limited'],
            ':featured' => $data['featured'],
            ':id' => $id,
        ]);

        get_products($pdo, $id);
    }

    if ($method === 'POST') {
        $data = validate_product(request_data());
        $image = upload_product_image($_FILES['image'] ?? null);

        $stmt = $pdo->prepare(
            'INSERT INTO products
             (name, description, price, category, image_path, stock, team, sizes, limited_edition, featured)
             VALUES (:name, :description, :price, :category, :image, :stock, :team, :sizes, :limited, :featured)'
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['desc'],
            ':price' => $data['price'],
            ':category' => $data['category'],
            ':image' => $image,
            ':stock' => $data['stock'],
            ':team' => $data['team'],
            ':sizes' => $data['sizes'],
            ':limited' => $data['limited'],
            ':featured' => $data['featured'],
        ]);

        get_products($pdo, (int)$pdo->lastInsertId());
    }

    if ($method === 'PUT') {
        if ($id < 1) {
            product_response(['success' => false, 'error' => 'Missing product id.'], 400);
        }

        $stmt = $pdo->prepare('SELECT image_path FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            product_response(['success' => false, 'error' => 'Product not found.'], 404);
        }

        $body = request_data();
        $data = validate_product($body);
        $image = $existing['image_path'];

        if (!empty($body['remove_image'])) {
            delete_product_image($existing['image_path']);
            $image = null;
        }

        $newImage = upload_product_image($_FILES['image'] ?? null);

        if ($newImage !== null) {
            delete_product_image($existing['image_path']);
            $image = $newImage;
        }

        $stmt = $pdo->prepare(
            'UPDATE products
             SET name = :name,
                 description = :description,
                 price = :price,
                 category = :category,
                 image_path = :image,
                 stock = :stock,
                 team = :team,
                 sizes = :sizes,
                 limited_edition = :limited,
                 featured = :featured
             WHERE id = :id'
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['desc'],
            ':price' => $data['price'],
            ':category' => $data['category'],
            ':image' => $image,
            ':stock' => $data['stock'],
            ':team' => $data['team'],
            ':sizes' => $data['sizes'],
            ':limited' => $data['limited'],
            ':featured' => $data['featured'],
            ':id' => $id,
        ]);

        get_products($pdo, $id);
    }

    if ($method === 'DELETE') {
        if ($id < 1) {
            product_response(['success' => false, 'error' => 'Missing product id.'], 400);
        }

        $stmt = $pdo->prepare('SELECT image_path FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();

        if (!$product) {
            product_response(['success' => false, 'error' => 'Product not found.'], 404);
        }

        $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
        delete_product_image($product['image_path']);

        product_response(['success' => true]);
    }

    product_response(['success' => false, 'error' => 'Method not allowed.'], 405);
} catch (Throwable $e) {
    error_log('[F1 MERCH products.php] ' . $e->getMessage());
    product_response([
        'success' => false,
        'error' => 'Unable to process the product request.',
    ], 500);
}
