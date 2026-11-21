<?php declare(strict_types=1);

require_once dirname(__DIR__) . "/vendor/autoload.php";
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

require_once dirname(__DIR__) . "/src/AppStorage.php";
require_once dirname(__DIR__) . "/src/CsvStorage.php";

function getStorage(): AppStorage
{
    static $storage = null;
    if ($storage instanceof AppStorage) {
        return $storage;
    }
    $env = static function (string $name, string $default = ""): string {
        $value = $_ENV[$name] ?? ($_SERVER[$name] ?? getenv($name));
        return is_string($value) && $value !== "" ? $value : $default;
    };
    $directory = $env("CSV_STORAGE_PATH");
    if ($directory === "") {
        $documentRoot = $_SERVER["DOCUMENT_ROOT"] ?? dirname(__DIR__);
        $documentRoot = realpath($documentRoot) ?: $documentRoot;
        $directory =
            dirname($documentRoot) . DIRECTORY_SEPARATOR . "setupdino-csv";
    } elseif (
        !preg_match("/^(?:[A-Za-z]:[\\\\\\/]|\\\\\\\\|\/)/", $directory)
    ) {
        $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . $directory;
    }
    return $storage = new CsvStorage($directory);
}
