<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
<<<<<<< HEAD

function getDatabaseConnection(): PDO {
    static $connection = null;
    if ($connection instanceof PDO) return $connection;
=======
require_once dirname(__DIR__) . '/src/AppStorage.php';
require_once dirname(__DIR__) . '/src/PdoStorage.php';
require_once dirname(__DIR__) . '/src/CsvStorage.php';

function getStorage(): AppStorage {
    static $storage = null;
    if ($storage instanceof AppStorage) return $storage;
>>>>>>> master
    $env = static function (string $name, string $default = ''): string {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
        return is_string($value) && $value !== '' ? $value : $default;
    };
<<<<<<< HEAD
=======
    $driver = strtolower($env('STORAGE_DRIVER', 'mysql'));
    if ($driver === 'csv') {
        $directory = $env('CSV_STORAGE_PATH');
        if ($directory === '') {
            $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
            $documentRoot = realpath($documentRoot) ?: $documentRoot;
            $directory = dirname($documentRoot) . DIRECTORY_SEPARATOR . 'setupdino-csv';
        } elseif (!preg_match('/^(?:[A-Za-z]:[\\\\\/]|\\\\\\\\|\/)/', $directory)) {
            $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . $directory;
        }
        return $storage = new CsvStorage($directory);
    }
    if ($driver !== 'mysql') {
        throw new RuntimeException('STORAGE_DRIVER must be either mysql or csv.');
    }
>>>>>>> master
    $host = $env('DB_HOST', 'localhost');
    $port = $env('DB_PORT', '3306');
    $database = $env('DB_NAME', 'SetupDino');
    $username = $env('DB_USER', 'SetupDino_app');
    $password = $env('DB_PASSWORD');
    if ($password === '') throw new RuntimeException('DB_PASSWORD environment variable is missing.');
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',$host,$port,$database);
<<<<<<< HEAD
    return $connection = new PDO($dsn,$username,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
=======
    $connection = new PDO($dsn,$username,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
    return $storage = new PdoStorage($connection);
>>>>>>> master
}
