<?php
declare(strict_types=1);
if (realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    http_response_code(404);
    exit('Page not found.');
}

require_once dirname(__DIR__).'/includes/auth.php';
$_SESSION=[];session_regenerate_id(true);session_destroy();header('Location: /');exit;
