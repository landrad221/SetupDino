<?php
declare(strict_types=1);

//require_once dirname(__DIR__) . '/vendor/autoload.php';
//Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

function getDatabaseConnection(): PDO {
    static $connection = null;
    if ($connection instanceof PDO) return $connection;
    $env = static function (string $name, string $default = ''): string {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
        return is_string($value) && $value !== '' ? $value : $default;
    };
    $host = $env('MYSQL_HOST', '127.0.0.1');
    $port = $env('MYSQL_PORT', '3306');
    $database = $env('MYSQL_DATABASE', 'SetupDino');
    $username = $env('MYSQL_USER', 'setupdino_user');
    $password = $env('MYSQL_PASSWORD');
    if ($password === '') throw new RuntimeException('MYSQL_PASSWORD environment variable is missing.');
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',$host,$port,$database);
    return $connection = new PDO($dsn,$username,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
}
