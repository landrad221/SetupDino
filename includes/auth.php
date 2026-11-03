<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) session_start(['use_strict_mode'=>true,'cookie_httponly'=>true,'cookie_samesite'=>'Lax','cookie_secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);

function isLoggedIn(): bool {
  return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
  return isLoggedIn() && ($_SESSION['role'] ?? null)==='admin';
}

function requireAdmin(): void {
  if (!isAdmin()) {
    http_response_code(403);
    exit('Access denied. Admin authentication is not implemented yet.');
  }
}

function requireLogin(): void {
  if (!isLoggedIn()) {
    header('Location: /login.php');
    exit;
  }
}
