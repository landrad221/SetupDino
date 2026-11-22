<?php declare(strict_types=1);

// Funções compartilhadas de sessão, perfil, login e CSRF para páginas protegidas.
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        "use_strict_mode" => true,
        "cookie_httponly" => true,
        "cookie_samesite" => "Lax",
        "cookie_secure" =>
            !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    ]);
}

/**
 * Indica se existe um usuário autenticado na sessão.
 *
 * @return bool Verdadeiro quando o identificador do usuário está presente.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION["user_id"]);
}

/**
 * Verifica se o usuário atual possui perfil de administrador.
 *
 * @return bool Verdadeiro quando o usuário está autenticado e é administrador.
 */
function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION["role"] ?? null) === "admin";
}

/**
 * Verifica se o usuário atual possui perfil de vendedor.
 *
 * @return bool Verdadeiro quando o usuário está autenticado e é vendedor.
 */
function isSeller(): bool
{
    return isLoggedIn() && ($_SESSION["role"] ?? null) === "seller";
}

/**
 * Interrompe a execução quando o usuário não é administrador.
 *
 * @return void Em caso de sucesso continua a execução; em caso de falha encerra com status 403.
 */
function requireAdmin(): void
{
    if (!isAdmin()) {
        http_response_code(403);
        exit("Acesso negado.");
    }
}

/**
 * Exige perfil de vendedor válido e atualiza a sessão quando o perfil mudou.
 *
 * @return void Continua a execução ou encerra a página com status 403.
 */
function requireSeller(): void
{
    if (!isSeller()) {
        http_response_code(403);
        exit("Acesso negado.");
    }
    require_once __DIR__ . "/../config/database.php";
    if (getStorage()->getUserRole((int) $_SESSION["user_id"]) !== "seller") {
        $_SESSION["role"] = "customer";
        http_response_code(403);
        exit("Acesso negado.");
    }
}

/**
 * Redireciona visitantes não autenticados para o login do checkout.
 *
 * @return void Continua ou redireciona a execução para /login.php.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header("Location: /login.php?next=checkout");
        exit();
    }
}

/**
 * Obtém ou cria o token CSRF da sessão atual.
 *
 * @return string Token aleatório hexadecimal usado em formulários.
 */
function csrfToken(): string
{
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf"];
}

/**
 * Valida um token CSRF recebido em uma operação sensível.
 *
 * @param string|null $token Token enviado pelo formulário.
 * @return void Continua quando válido; encerra com status 419 quando inválido.
 */
function verifyCsrf(?string $token): void
{
    if (!is_string($token) || !hash_equals(csrfToken(), $token)) {
        http_response_code(419);
        exit("Token do formulário inválido. Atualize a página e tente novamente.");
    }
}
