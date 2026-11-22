
<?php
// Detect local XAMPP environment
$isLocal = in_array(
    $_SERVER['SERVER_NAME'] ?? 'localhost',
    ['localhost', '127.0.0.1', '::1']
);

if ($isLocal) {
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'netusage_hub');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('DB_HOST', getenv('DB_HOST') ?: '');
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
    define('DB_NAME', getenv('DB_NAME') ?: '');
    define('DB_USER', getenv('DB_USER') ?: '');
    define('DB_PASS', getenv('DB_PASS') ?: '');
}

define('APP_NAME', 'NetUsage Hub');

date_default_timezone_set('Africa/Nairobi');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        if (!DB_HOST || !DB_NAME || !DB_USER) {
            throw new RuntimeException(
                'Database environment variables are missing.'
            );
        }

        $pdo = new PDO(
            'mysql:host=' . DB_HOST .
            ';port=' . DB_PORT .
            ';dbname=' . DB_NAME .
            ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }

    return $pdo;
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function verify_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Invalid request token. Please go back and try again.');
    }
}

