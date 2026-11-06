<?php
declare(strict_types=1);
function getDatabaseConnection(): PDO {
    static $connection = null;
    if ($connection instanceof PDO) return $connection;
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_NAME') ?: 'gamersells';
    $username = getenv('DB_USER') ?: 'gamersells_app';
    $password = getenv('DB_PASSWORD') ?: 'REPLACE_WITH_STRONG_PASSWORD' ;
    if ($password === false || $password === '') throw new RuntimeException('DB_PASSWORD environment variable is missing.');
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',$host,$port,$database);
    return $connection = new PDO($dsn,$username,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
}
