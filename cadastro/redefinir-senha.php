<?php
require_once __DIR__ . '/../src/conexao.php';
require_once __DIR__ . '/../src/sessao.php';
require_once __DIR__ . '/../src/recuperacao-senha.php';

header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');

$token = $_GET['token'] ?? '';
$tokenValido = is_string($token) && tokenRecuperacaoTemFormatoValido($token);
$recuperacao = null;
$erroToken = '';
$erroFormulario = match ($_GET['erro'] ?? '') {
    'senha' => 'A senha deve ter entre 8 e 72 bytes.',
    'confirmacao' => 'As senhas não conferem.',
    'igual' => 'Escolha uma senha diferente da senha atual.',
    'indisponivel' => 'Não foi possível redefinir a senha agora. Tente novamente.',
    default => '',
};

if ($tokenValido) {
    try {
        $recuperacao = buscarRecuperacaoAtiva($pdo, hash('sha256', $token));
    } catch (PDOException $e) {
        error_log('Falha ao validar token de redefinição de senha: ' . $e->getMessage());
        http_response_code(500);
        $erroToken = 'Não foi possível validar este link agora. Tente novamente mais tarde.';
    }
}

if ($erroToken === '' && !$recuperacao) {
    $erroToken = 'Este link é inválido, expirou ou já foi utilizado. Solicite um novo link.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir senha - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/login.css">
    <link rel="stylesheet" href="../CSS/mobile.css">
</head>
<body>
    <nav class="navbar">
        <a href="../index.php" class="navbar-logo">
            <img src="../images/logo-claro-infodin.png" alt="Logo Infodin" class="logo-light">
            <img src="../images/logo-infodin.png" alt="Logo Infodin" class="logo-dark">
        </a>
        <button id="theme-toggle" class="theme-toggle" type="button" title="Mudar tema" aria-label="Alternar tema">
            <span aria-hidden="true">◐</span>
        </button>
    </nav>

    <main class="login-wrapper">
        <section class="login-card" aria-labelledby="titulo-redefinir-senha">
            <header class="login-header">
                <h1 class="login-title" id="titulo-redefinir-senha">Redefinir senha</h1>
                <p class="login-subtitle">Escolha uma nova senha para sua conta.</p>
            </header>

            <?php if ($erroToken !== ''): ?>
                <div class="form-error" role="alert"><?= htmlspecialchars($erroToken, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="register-link">
                    <a href="recuperar-senha.php">Solicitar outro link</a>
                </div>
            <?php else: ?>
                <?php if ($erroFormulario !== ''): ?>
                    <div class="form-error" role="alert"><?= htmlspecialchars($erroFormulario, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form action="../src/processar-redefinicao-senha.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="form-group">
                        <label class="form-label" for="senha-nova">Nova senha</label>
                        <div class="password-field">
                            <input
                                id="senha-nova"
                                type="password"
                                name="senha"
                                class="form-input"
                                minlength="8"
                                maxlength="72"
                                autocomplete="new-password"
                                required
                            >
                            <button type="button" class="password-toggle" aria-label="Mostrar senha" aria-controls="senha-nova" aria-pressed="false">Mostrar</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirmar-senha">Confirme a nova senha</label>
                        <div class="password-field">
                            <input
                                id="confirmar-senha"
                                type="password"
                                name="confirmar_senha"
                                class="form-input"
                                minlength="8"
                                maxlength="72"
                                autocomplete="new-password"
                                required
                            >
                            <button type="button" class="password-toggle" aria-label="Mostrar senha" aria-controls="confirmar-senha" aria-pressed="false">Mostrar</button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Salvar nova senha</button>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <script src="../JS/tema.js"></script>
    <script src="../JS/alternar-senha.js"></script>
</body>
</html>
