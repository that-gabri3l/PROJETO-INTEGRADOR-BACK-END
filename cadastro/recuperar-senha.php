<?php
require_once __DIR__ . '/../src/sessao.php';

$erro = match ($_GET['erro'] ?? '') {
    'email' => 'Informe um endereço de e-mail válido.',
    'indisponivel' => 'Não foi possível processar o pedido agora. Tente novamente mais tarde.',
    default => '',
};
$pedidoEnviado = isset($_GET['enviado']) && $_GET['enviado'] === '1';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar senha - Infodin</title>
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
        <section class="login-card" aria-labelledby="titulo-recuperar-senha">
            <header class="login-header">
                <h1 class="login-title" id="titulo-recuperar-senha">Recuperar senha</h1>
                <p class="login-subtitle">Informe o e-mail usado na sua conta.</p>
            </header>

            <?php if ($erro !== ''): ?>
                <div class="form-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if ($pedidoEnviado): ?>
                <div class="form-success" role="status">
                    Se houver uma conta com esse e-mail, enviaremos um link para redefinir a senha.
                </div>
            <?php endif; ?>

            <form action="../src/solicitar-recuperacao-senha.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group">
                    <label class="form-label" for="email-recuperacao">E-mail</label>
                    <input
                        id="email-recuperacao"
                        type="email"
                        name="email"
                        class="form-input"
                        placeholder="seu@email.com"
                        maxlength="255"
                        autocomplete="email"
                        required
                    >
                </div>

                <button type="submit" class="btn-primary">Enviar instruções</button>
            </form>

            <div class="register-link">
                <a href="login.php">Voltar para o login</a>
            </div>
        </section>
    </main>

    <script src="../JS/tema.js"></script>
</body>
</html>
