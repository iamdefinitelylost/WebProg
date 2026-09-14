<?php
// F1 MERCH authentication API
// Every response from this file is JSON. PHP errors are captured so they cannot
// turn a successful HTTP response into broken/non-JSON output.
ob_start();
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

function clean_output(): void {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}

function json_response(array $data, int $status = 200): void {
    clean_output();
    http_response_code($status);
    $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        $json = '{"error":"Unable to encode server response."}';
        http_response_code(500);
    }
    echo $json;
    exit;
}

set_exception_handler(function (Throwable $e): void {
    json_response([
        'success' => false,
        'error' => 'Authentication server error.',
        'debug' => $e->getMessage(),
    ], 500);
});

register_shutdown_function(function (): void {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        json_response([
            'success' => false,
            'error' => 'Authentication server error.',
            'debug' => $e['message'],
        ], 500);
    }
});

function start_auth_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $params = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $params['path'] ?: '/',
        'domain' => $params['domain'] ?? '',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if (!session_start()) {
        throw new RuntimeException('PHP session could not be started. Check your PHP session configuration.');
    }
}

function request_body(): array {
    $raw = file_get_contents('php://input');
    if ($raw !== false && trim($raw) !== '') {
        $data = json_decode($raw, true);
        if (is_array($data)) {
            return $data;
        }
    }
    return is_array($_POST) ? $_POST : [];
}

function user_public(array $u): array {
    return [
        'id' => (int)$u['id'],
        'name' => (string)$u['name'],
        'email' => (string)$u['email'],
        'role' => strtolower(trim((string)$u['role'])),
        'banned' => !empty($u['banned_at']),
        'avatar_path' => $u['avatar_path'] ?? null,
        'bio' => $u['bio'] ?? '',
        'favorite_team' => $u['favorite_team'] ?? '',
    ];
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf_token'];
}

function require_csrf(): void {
    $token = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($token === '' || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        json_response(['success' => false, 'error' => 'Security token expired. Refresh the page and try again.'], 419);
    }
}

function require_admin(): void {
    $role = strtolower(trim((string)($_SESSION['user']['role'] ?? '')));
    if (empty($_SESSION['user']) || $role !== 'admin' || empty($_SESSION['admin_authenticated'])) {
        json_response(['success' => false, 'error' => 'Administrator login required.'], 401);
    }
}

try {
    start_auth_session();
    require_once __DIR__ . '/../db.php';
    // CSRF token is used for authenticated state-changing requests.
    if (isset($_GET['action']) && $_GET['action'] === 'csrf') {
        json_response(['success' => true, 'csrf_token' => csrf_token()]);
    }

    $action = strtolower(trim((string)($_GET['action'] ?? '')));

    switch ($action) {
        case 'me':
            if (empty($_SESSION['user'])) {
                json_response(['success' => true, 'authenticated' => false, 'user' => null, 'authorized_role' => null]);
            }

            $sessionUser = $_SESSION['user'];
            $role = strtolower(trim((string)($sessionUser['role'] ?? '')));

            // Re-check the customer ban status on every session lookup.
            if ($role === 'customer') {
                $stmt = $pdo->prepare('SELECT banned_at FROM users WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => (int)$sessionUser['id']]);
                $status = $stmt->fetch();
                if (!$status || !empty($status['banned_at'])) {
                    $_SESSION = [];
                    session_destroy();
                    json_response(['success' => true, 'authenticated' => false, 'user' => null, 'authorized_role' => null, 'banned' => true]);
                }
            }

            json_response([
                'success' => true,
                'authenticated' => true,
                'user' => $sessionUser,
                'authorized_role' => $role,
            ]);
            break;

        case 'login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_response(['success' => false, 'error' => 'Method not allowed.'], 405);
            }

            $data = request_body();
            $email = strtolower(trim((string)($data['email'] ?? '')));
            $password = (string)($data['password'] ?? '');
            $requestedRole = strtolower(trim((string)($data['role'] ?? 'customer')));

            if (!in_array($requestedRole, ['customer', 'admin'], true)) {
                json_response(['success' => false, 'error' => 'Invalid account type.'], 422);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
                json_response(['success' => false, 'error' => 'Enter your email and password.'], 422);
            }

            // Core columns only. This keeps login working even if optional profile
            // columns were not added yet to an older database.
            $stmt = $pdo->prepare('SELECT id, name, email, password_hash, role, banned_at FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, (string)$user['password_hash'])) {
                json_response(['success' => false, 'error' => 'Invalid email or password.'], 401);
            }

            $actualRole = strtolower(trim((string)$user['role']));
            if ($actualRole !== $requestedRole) {
                json_response([
                    'success' => false,
                    'error' => $requestedRole === 'admin'
                        ? 'This account is not an administrator account.'
                        : 'Please use the customer sign-in page for this account.'
                ], 403);
            }

            if ($actualRole === 'customer' && !empty($user['banned_at'])) {
                json_response(['success' => false, 'error' => 'Your account has been banned. Please contact support.'], 403);
            }

            if (!session_regenerate_id(true)) {
                throw new RuntimeException('PHP could not regenerate the login session. Check your PHP session configuration.');
            }

            $public = [
                'id' => (int)$user['id'],
                'name' => (string)$user['name'],
                'email' => (string)$user['email'],
                'role' => $actualRole,
                'banned' => !empty($user['banned_at']),
                'avatar_path' => null,
                'bio' => '',
                'favorite_team' => '',
            ];

            // Optional profile fields are loaded only if they exist.
            try {
                $profileStmt = $pdo->prepare('SELECT avatar_path, bio, favorite_team FROM users WHERE id = :id LIMIT 1');
                $profileStmt->execute([':id' => (int)$user['id']]);
                if ($profile = $profileStmt->fetch()) {
                    $public['avatar_path'] = $profile['avatar_path'] ?? null;
                    $public['bio'] = $profile['bio'] ?? '';
                    $public['favorite_team'] = $profile['favorite_team'] ?? '';
                }
            } catch (Throwable $ignored) {
                // Login itself does not depend on optional profile columns.
            }

            $_SESSION['user'] = $public;
            $_SESSION['admin_authenticated'] = $actualRole === 'admin';
            session_write_close();

            json_response([
                'success' => true,
                'authenticated' => true,
                'authorized_role' => $actualRole,
                'user' => $public,
            ]);
            break;

        case 'signup':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_response(['success' => false, 'error' => 'Method not allowed.'], 405);
            }

            $data = request_body();
            $name = trim((string)($data['name'] ?? ''));
            $email = strtolower(trim((string)($data['email'] ?? '')));
            $password = (string)($data['password'] ?? '');

            if (function_exists('mb_strlen')) {
                $nameLength = mb_strlen($name);
            } else {
                $nameLength = strlen($name);
            }
            if ($nameLength < 2) {
                json_response(['success' => false, 'error' => 'Please enter your name.'], 422);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                json_response(['success' => false, 'error' => 'Please enter a valid email address.'], 422);
            }
            if (strlen($password) < 8) {
                json_response(['success' => false, 'error' => 'Password must be at least 8 characters.'], 422);
            }

            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                json_response(['success' => false, 'error' => 'An account with that email already exists.'], 409);
            }

            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :hash, :role)');
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':hash' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => 'customer',
            ]);

            session_regenerate_id(true);
            $public = [
                'id' => (int)$pdo->lastInsertId(),
                'name' => $name,
                'email' => $email,
                'role' => 'customer',
                'banned' => false,
                'avatar_path' => null,
                'bio' => '',
                'favorite_team' => '',
            ];
            $_SESSION['user'] = $public;
            $_SESSION['admin_authenticated'] = false;
            session_write_close();

            json_response(['success' => true, 'authenticated' => true, 'authorized_role' => 'customer', 'user' => $public], 201);
            break;

        case 'logout':
            require_csrf();
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();
            json_response(['success' => true, 'authenticated' => false]);
            break;

        case 'users':
            require_admin();
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                json_response(['success' => false, 'error' => 'Method not allowed.'], 405);
            }
            $stmt = $pdo->query("SELECT u.id,u.name,u.email,u.role,u.banned_at,u.avatar_path,u.created_at,COUNT(o.id) order_count,COALESCE(SUM(CASE WHEN o.status<>'cancelled' THEN o.total ELSE 0 END),0) order_total FROM users u LEFT JOIN orders o ON o.user_id=u.id GROUP BY u.id,u.name,u.email,u.role,u.banned_at,u.avatar_path,u.created_at ORDER BY u.created_at DESC");
            $users = $stmt->fetchAll();
            foreach ($users as &$u) {
                $u['id'] = (int)$u['id'];
                $u['banned'] = !empty($u['banned_at']);
                unset($u['banned_at']);
                $u['order_count'] = (int)($u['order_count'] ?? 0);
                $u['order_total'] = (float)($u['order_total'] ?? 0);
            }
            json_response(['success' => true, 'users' => $users]);
            break;

        case 'ban':
            require_admin();
            require_csrf();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_response(['success' => false, 'error' => 'Method not allowed.'], 405);
            }
            $data = request_body();
            $id = (int)($data['id'] ?? 0);
            $ban = (bool)($data['ban'] ?? true);
            if (!$id) {
                json_response(['success' => false, 'error' => 'Missing user id.'], 422);
            }
            if ((int)($_SESSION['user']['id'] ?? 0) === $id) {
                json_response(['success' => false, 'error' => 'You cannot ban your own administrator account.'], 422);
            }
            $stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $target = $stmt->fetch();
            if (!$target) {
                json_response(['success' => false, 'error' => 'User not found.'], 404);
            }
            if (strtolower(trim((string)$target['role'])) === 'admin') {
                json_response(['success' => false, 'error' => 'Administrator accounts cannot be banned here.'], 422);
            }
            $sql = $ban
                ? 'UPDATE users SET banned_at = CURRENT_TIMESTAMP WHERE id = :id'
                : 'UPDATE users SET banned_at = NULL WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            json_response(['success' => true, 'banned' => $ban]);
            break;

        case 'profile':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'customer') {
                json_response(['success' => false, 'error' => 'Customer login required.'], 401);
            }
            $id = (int)$_SESSION['user']['id'];
            $name = trim((string)($_POST['name'] ?? ''));
            $bio = trim((string)($_POST['bio'] ?? ''));
            $team = trim((string)($_POST['favorite_team'] ?? ''));
            $nameLength = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);
            $bioLength = function_exists('mb_strlen') ? mb_strlen($bio) : strlen($bio);
            $teamLength = function_exists('mb_strlen') ? mb_strlen($team) : strlen($team);
            if ($nameLength < 2) json_response(['success'=>false,'error'=>'Display name must be at least 2 characters.'],422);
            if ($bioLength > 240 || $teamLength > 80) json_response(['success'=>false,'error'=>'Profile text is too long.'],422);
            $avatar = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) json_response(['success'=>false,'error'=>'Unable to upload profile photo.'],422);
                if ($_FILES['avatar']['size'] > 5 * 1024 * 1024) json_response(['success'=>false,'error'=>'Profile photo must be 5 MB or smaller.'],422);
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES['avatar']['tmp_name']);
                $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
                if (!isset($allowed[$mime])) json_response(['success'=>false,'error'=>'Profile photo must be JPG, PNG, or WebP.'],422);
                $dir = __DIR__ . '/../uploads/profiles';
                if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) json_response(['success'=>false,'error'=>'Unable to create profile upload folder.'],500);
                $file = 'profile_' . $id . '_' . bin2hex(random_bytes(5)) . '.' . $allowed[$mime];
                if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . '/' . $file)) json_response(['success'=>false,'error'=>'Unable to save profile photo.'],500);
                $avatar = 'uploads/profiles/' . $file;
            }
            try {
                if ($avatar !== null) {
                    $stmt = $pdo->prepare('UPDATE users SET name=:name,bio=:bio,favorite_team=:team,avatar_path=:avatar WHERE id=:id');
                    $stmt->execute([':name'=>$name,':bio'=>$bio,':team'=>$team,':avatar'=>$avatar,':id'=>$id]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET name=:name,bio=:bio,favorite_team=:team WHERE id=:id');
                    $stmt->execute([':name'=>$name,':bio'=>$bio,':team'=>$team,':id'=>$id]);
                }
                $stmt = $pdo->prepare('SELECT id,name,email,role,banned_at,avatar_path,bio,favorite_team FROM users WHERE id=:id LIMIT 1');
                $stmt->execute([':id'=>$id]);
                $u = $stmt->fetch();
            } catch (Throwable $e) {
                json_response(['success'=>false,'error'=>'Profile fields are missing from the users table. Run the updated schema.sql.','debug'=>$e->getMessage()],500);
            }
            $_SESSION['user'] = user_public($u);
            json_response(['success'=>true,'user'=>$_SESSION['user']]);
            break;

        default:
            json_response(['success' => false, 'error' => 'Unknown authentication action.'], 400);
    }
} catch (Throwable $e) {
    json_response([
        'success' => false,
        'error' => 'Authentication server error.',
        'debug' => $e->getMessage(),
    ], 500);
}
