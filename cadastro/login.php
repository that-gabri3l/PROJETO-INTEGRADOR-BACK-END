<?php
require_once __DIR__ . '/../src/sessao.php';

$erro = isset($_GET['erro']) ? trim((string) $_GET['erro']) : '';
$sucesso = isset($_GET['cadastro']) && $_GET['cadastro'] === 'sucesso';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/CSS/login.css">
    <link rel="stylesheet" href="/CSS/mobile.css">
    
</head>
<body>

    <!-- Navbar Minimalista para a tela de Login -->
    <nav class="navbar">
        <a href="/index.php" class="navbar-logo">
            <!-- Coloque o nome da sua logo clara e escura aqui -->
            <img src="/images/logo-claro-infodin.png" alt="Logo do site" class="logo-light" style="height: 36px; width: auto;">
            <img src="/images/logo-infodin.png" alt="Logo do site" class="logo-dark" style="height: 36px; width: auto;">
        </a>
        <button id="theme-toggle" class="theme-toggle" title="Mudar tema" aria-label="Alternar tema"><svg class="bi bi-moon-fill" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/></svg></button>
    </nav>

    <!-- Área de Login -->
    <main class="login-wrapper">
        <div class="login-card">
            
            <div class="login-header">
                <h1 class="login-title">Bem-vindo de volta</h1>
                <p class="login-subtitle">Faça login para acessar o seu painel.</p>
            </div>

            <!-- Formulário apontando para a rota de autenticação -->
            <form action="/src/processar-login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($erro !== ''): ?>
                    <div class="form-error" role="alert">
                        <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($sucesso): ?>
                    <div class="form-success" role="status">Cadastro concluído! Agora você já pode entrar.</div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-input" placeholder="seu@email.com" required>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <label for="senha-login">Senha</label>
                        <a href="#" class="forgot-password">Esqueceu a senha?</a>
                    </div>
                    <div class="password-field">
                        <input id="senha-login" type="password" name="senha" class="form-input" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" aria-label="Mostrar senha" aria-controls="senha-login" aria-pressed="false">Mostrar</button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Entrar</button>

            </form>

            <div class="register-link">
    Ainda não tem uma conta? <br>
    <a href="/candidato/cadastro.php">Cadastrar como Candidato</a> ou 
    <a href="/empresa/cadastro.php">Empresa</a>
</div>

        </div>
    </main>

    <!-- Script de Modo Escuro -->
        <script src="/JS/tema.js"></script>
        <script src="/JS/alternar-senha.js"></script>

</body>
</html>