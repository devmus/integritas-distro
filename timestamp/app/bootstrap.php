<?php declare(strict_types=1);

/**
 * Helper to fetch env vars with a default.
 */
function env(string $key, ?string $default = null): ?string {
    $v = getenv($key);
    return ($v === false || $v === '') ? $default : $v;
}

/**
 * Central place to build a PDO to the Timestamp DB.
 * Uses env injected via docker-compose:
 *   TIMESTAMP_DB_HOST, TIMESTAMP_DB_NAME, TIMESTAMP_DB_USER, TIMESTAMP_DB_PASSWORD
 */
function timestamp_pdo(): PDO {
    $host = env('TIMESTAMP_DB_HOST', 'timestamp-mysql');
    $db   = env('TIMESTAMP_DB_NAME', 'tutorial');
    $user = env('TIMESTAMP_DB_USER', 'tutorial');
    $pass = env('TIMESTAMP_DB_PASSWORD', '');
    $port = env('TIMESTAMP_DB_PORT', '3306'); // optional, default 3306

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $user, $pass, $options);
}
