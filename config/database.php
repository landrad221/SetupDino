<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

function getDatabaseConnection(): PDO {
    static $connection = null;
    if ($connection instanceof PDO) return $connection;
    $env = static function (string $name, string $default = ''): string {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
        return is_string($value) && $value !== '' ? $value : $default;
    };
    $host = $env('DB_HOST', 'localhost');
    $port = $env('DB_PORT', '3306');
    $database = $env('DB_NAME', 'SetupDino');
    $username = $env('DB_USER', 'SetupDino_app');
    $password = $env('DB_PASSWORD');
    if ($password === '') throw new RuntimeException('DB_PASSWORD environment variable is missing.');
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',$host,$port,$database);
    return $connection = new PDO($dsn,$username,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
}
