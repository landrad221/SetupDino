<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $assetsRoot = realpath(__DIR__ . '/assets');
    $requestedFile = is_string($requestPath)
        ? realpath(__DIR__ . rawurldecode($requestPath))
        : false;

    if (
        $assetsRoot !== false
        && $requestedFile !== false
        && is_file($requestedFile)
        && str_starts_with($requestedFile, $assetsRoot . DIRECTORY_SEPARATOR)
    ) {
        return false;
    }
}

require_once __DIR__ . '/routes/RouteController.php';
$routeFile = RouteController::resolve((string)($_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_NAME'] ?? ''));
require $routeFile;
