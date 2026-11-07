<?php
declare(strict_types=1);

function getDatabaseConnection(): PDO {
    static $connection = null;
    if ($connection instanceof PDO) return $connection;
    $host = $_ENV['DB_HOST'] ?: 'localhost';
    $port = $_ENV['DB_PORT'] ?: '3306';
    $database = $_ENV['DB_NAME'] ?: 'SetupDino';
    $username = $_ENV['DB_USER'] ?: 'SetupDino_app';
    $password = $_ENV['DB_PASSWORD'];
    if ($password === false || $password === '') throw new RuntimeException('DB_PASSWORD environment variable is missing.');
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',$host,$port,$database);
    return $connection = new PDO($dsn,$username,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
}
