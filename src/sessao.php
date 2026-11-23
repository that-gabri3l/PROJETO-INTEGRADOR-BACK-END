<?php

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function usuarioEstaLogado(): bool
{
    return isset($_SESSION['usuario_id'], $_SESSION['usuario_tipo']);
}

function exigirLogin(): void
{
    if (!usuarioEstaLogado()) {
        header('Location: /cadastro/login.php?erro=' . urlencode('Faça login para continuar.'));
        exit;
    }
}

function exigirTipo(string $tipo): void
{
    exigirLogin();

    if ($_SESSION['usuario_tipo'] !== $tipo) {
        http_response_code(403);
        header('Location: /index.php');
        exit;
    }
}

function fazerLogout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametros['path'],
            $parametros['domain'],
            $parametros['secure'],
            $parametros['httponly']
        );
    }

    session_destroy();
}

function gerarTokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validarTokenCsrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function exigirTokenCsrf(): void
{
    $token = $_POST['csrf_token'] ?? null;
    if (!validarTokenCsrf(is_string($token) ? $token : null)) {
        http_response_code(403);
        exit('Requisição inválida. Recarregue a página e tente novamente.');
    }
}
