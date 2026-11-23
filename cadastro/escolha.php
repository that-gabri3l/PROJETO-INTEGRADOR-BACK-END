<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escolha o seu perfil - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
     <link rel="stylesheet" href="/CSS/escolha-cadastro.css">
     <link rel="stylesheet" href="/CSS/mobile.css">
   
</head>
<body>

    <nav class="navbar">
        <a href="/index.php" class="navbar-logo"><!-- Imagem para o Modo Claro -->
            <img src="/images/logo-claro-infodin.png" alt="Logo do site" class="logo-light"
                style="height:40px; width: auto; object-fit: contain;">

            <!-- Imagem para o Modo Escuro -->
            <img src="/images/logo-infodin.png" alt="Logo do site" class="logo-dark"
                style="height: 40px; width: auto; object-fit: contain;"></a>
        <button id="theme-toggle" class="theme-toggle" title="Mudar tema" aria-label="Alternar tema"><svg class="bi bi-moon-fill" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/></svg></button>
    </nav>

    <main class="split-layout">
        <!-- Lado do Candidato -->
        <a href="/candidato/cadastro.php" class="split-card candidate-side">
            <div class="bg-number">01</div>
            
            <div class="content-wrapper">
                <span class="eyebrow">Para candidatos</span>
                <div class="icon-box">👤</div>
                
                <h2 class="main-title">
                    Sou<br><em>candidato</em>
                </h2>
                
                <p class="description">
                    Crie seu perfil profissional, envie candidaturas com um clique e acompanhe o status de cada processo seletivo.
                </p>

                <ul class="benefits-list">
                    <li><span class="check-icon">✓</span> Cadastro gratuito e sem complicações</li>
                    <li><span class="check-icon">✓</span> Acesso a mais de 4.800 vagas ativas</li>
                    <li><span class="check-icon">✓</span> Candidatura com 1 clique</li>
                    <li><span class="check-icon">✓</span> Acompanhamento em tempo real</li>
                </ul>

                <div class="btn-action">
                    Cadastrar como candidato <span style="font-size: 16px;">→</span>
                </div>
            </div>
        </a>

        <!-- Lado da Empresa -->
        <a href="/empresa/cadastro.php" class="split-card company-side">
            <div class="bg-number">02</div>
            
            <div class="content-wrapper">
                <span class="eyebrow">Para empresas</span>
                <div class="icon-box">🏢</div>
                
                <h2 class="main-title">
                    Sou<br><em>empresa</em>
                </h2>
                
                <p class="description">
                    Publique vagas, gerencie candidatos e encontre os melhores talentos do Brasil de forma simples e eficiente.
                </p>

                <ul class="benefits-list">
                    <li><span class="check-icon">✓</span> Publicação ilimitada de vagas</li>
                    <li><span class="check-icon">✓</span> Painel completo de gestão</li>
                    <li><span class="check-icon">✓</span> Filtros avançados de candidatos</li>
                    <li><span class="check-icon">✓</span> Relatórios e métricas em tempo real</li>
                </ul>

                <div class="btn-action">
                    Cadastrar minha empresa <span style="font-size: 16px;">→</span>
                </div>
            </div>
        </a>
    </main>

    <!-- Barra Inferior -->
    <footer class="bottom-bar">
        <span class="bottom-text">
            Já tem uma conta? <a href="/candidato/painel.php" class="login-link">Entrar</a>
        </span>
    </footer>

        <script src="/JS/tema.js"></script>

</body>
</html>