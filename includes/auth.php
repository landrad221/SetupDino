<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start(['use_strict_mode'=>true,'cookie_httponly'=>true,'cookie_samesite'=>'Lax','cookie_secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
}
function isLoggedIn(): bool { return isset($_SESSION['user_id']); }
function isAdmin(): bool { return isLoggedIn() && ($_SESSION['role'] ?? null) === 'admin'; }
function isSeller(): bool { return isLoggedIn() && ($_SESSION['role'] ?? null) === 'seller'; }
function requireAdmin(): void { if (!isAdmin()) { http_response_code(403); exit('Access denied.'); } }
function requireSeller(): void {
    if (!isSeller()) { http_response_code(403); exit('Access denied.'); }
    require_once __DIR__ . '/../config/database.php';
    if (getStorage()->getUserRole((int)$_SESSION['user_id']) !== 'seller') {
        $_SESSION['role'] = 'customer';
        http_response_code(403);
        exit('Access denied.');
    }
}
function requireLogin(): void { if (!isLoggedIn()) { header('Location: /login.php?next=checkout'); exit; } }
function csrfToken(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verifyCsrf(?string $token): void { if (!is_string($token) || !hash_equals(csrfToken(), $token)) { http_response_code(419); exit('Invalid form token. Refresh and try again.'); } }
