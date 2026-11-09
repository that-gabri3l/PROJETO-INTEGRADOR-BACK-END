<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/CSS/login.css">
    
</head>
<body>

    <!-- Navbar Minimalista para a tela de Login -->
    <nav class="navbar">
        <a href="/index.php" class="navbar-logo">
            <!-- Coloque o nome da sua logo clara e escura aqui -->
            <img src="/images/logo-claro-infodin.png" alt="Logo do site" class="logo-light" style="height: 36px; width: auto;">
            <img src="/images/logo-infodin.png" alt="Logo do site" class="logo-dark" style="height: 36px; width: auto;">
        </a>
        <button id="theme-toggle" class="theme-toggle" title="Mudar tema">🌗</button>
    </nav>

    <!-- Área de Login -->
    <main class="login-wrapper">
        <div class="login-card">
            
            <div class="login-header">
                <h1 class="login-title">Bem-vindo de volta</h1>
                <p class="login-subtitle">Faça login para acessar o seu painel.</p>
            </div>

            <!-- Formulário apontando para a rota de autenticação -->
            <form action="/api/login" method="POST">
                
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-input" placeholder="seu@email.com" required>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <span>Senha</span>
                        <a href="#" class="forgot-password">Esqueceu a senha?</a>
                    </div>
                    <input type="password" name="senha" class="form-input" placeholder="••••••••" required>
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
        <script src="/js/tema.js"></script>

</body>
</html>