<?php
require_once __DIR__ . '/src/sessao.php';
require_once __DIR__ . '/src/conexao.php';

$usuarioLogado = usuarioEstaLogado();
$usuarioTipo = $_SESSION['usuario_tipo'] ?? null;
$usuarioNome = $_SESSION['usuario_nome'] ?? '';
$busca = trim($_GET['q'] ?? '');

$areasDestaque = ['Tecnologia', 'Design', 'Marketing', 'Dados', 'Gestão', 'Vendas', 'Jurídico', 'Financeiro', 'RH'];
$areaSelecionada = trim($_GET['area'] ?? '');
if (!in_array($areaSelecionada, $areasDestaque, true)) {
    $areaSelecionada = '';
}

$sqlDestaques = 'SELECT v.id, v.titulo, v.area, v.regime, v.localizacao, v.salario_min, v.salario_max,
                        v.exibir_salario, e.nome AS empresa_nome
                FROM vagas v
                INNER JOIN empresas e ON e.id = v.empresa_id
                
                WHERE v.status = :status';
$parametrosDestaques = ['status' => 'ativa'];

if ($areaSelecionada !== '') {
    $sqlDestaques .= ' AND v.area = :area';
    $parametrosDestaques['area'] = $areaSelecionada;
}

$sqlDestaques .= ' ORDER BY v.criado_em DESC LIMIT 3';
$stmtDestaques = $pdo->prepare($sqlDestaques);
$stmtDestaques->execute($parametrosDestaques);
$vagasDestaque = $stmtDestaques->fetchAll();

$estatisticas = $pdo->query(
    "SELECT
        (SELECT COUNT(*) FROM vagas WHERE status = 'ativa') AS vagas_ativas,
        (SELECT COUNT(*) FROM empresas) AS empresas_cadastradas,
        (SELECT COUNT(*) FROM candidatos) AS candidatos_cadastrados,
        (SELECT COUNT(*) FROM candidaturas) AS candidaturas_total,
        (SELECT COUNT(*) FROM candidaturas WHERE status <> 'recebida') AS candidaturas_respondidas"
)->fetch();
$taxaResposta = (int) $estatisticas['candidaturas_total'] > 0
    ? round(((int) $estatisticas['candidaturas_respondidas'] / (int) $estatisticas['candidaturas_total']) * 100)
    : null;

$empresasDestaque = $pdo->query(
    "SELECT e.nome, e.setor, COUNT(v.id) AS vagas_ativas
     FROM empresas e
     INNER JOIN vagas v ON v.empresa_id = e.id AND v.status = 'ativa'
     GROUP BY e.id, e.nome, e.setor
     ORDER BY vagas_ativas DESC, e.nome ASC
     LIMIT 6"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infodin - Encontre a vaga certa para você</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="CSS/index.css">    
    <link rel="stylesheet" href="/CSS/mobile.css">

</head>

<body>

    <nav class="navbar navbar--menu">
        <a href="index.php" class="logo">
            <!-- Imagem para o Modo Claro -->
            <img src="images/logo-claro-infodin.png" alt="Logo do site" class="logo-light"
                style="height:40px; width: auto; object-fit: contain;">

            <!-- Imagem para o Modo Escuro -->
            <img src="images/logo-infodin.png" alt="Logo do site" class="logo-dark"
                style="height: 40px; width: auto; object-fit: contain;">
        </a>

        <!-- Checkbox invisível que controla o menu -->
        <input type="checkbox" id="menu-toggle" class="menu-toggle">
        <!-- Ícone do Hambúrguer (associado ao checkbox) -->
        <label for="menu-toggle" class="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </label>

        <div class="menu-links">
            <a href="vagas/index.php" class="nav-link">Buscar Vagas</a>
            <?php if (!$usuarioLogado): ?>
                <a href="empresa/cadastro.php" class="nav-link">Para Empresas</a>
                <a href="candidato/cadastro.php" class="nav-link">Cadastrar</a>
                <a href="cadastro/login.php" class="nav-btn">Entrar</a>
            <?php else: ?>
                <a href="<?= $usuarioTipo === 'empresa' ? '/empresa/painel.php' : '/candidato/painel.php' ?>" class="nav-btn"><?= htmlspecialchars($usuarioNome !== '' ? $usuarioNome : 'Meu painel', ENT_QUOTES, 'UTF-8') ?></a>
                <a href="/src/logout.php" class="nav-link">Sair</a>
            <?php endif; ?>

            <button id="theme-toggle" class="theme-toggle" title="Mudar tema">🌗</button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-pattern"></div>
        <div class="hero-content">
            <div style="max-width: 640px;">
                <div class="hero-label">
                    <div class="hero-label-line"></div>
                    <span class="hero-label-text">Plataforma de empregos</span>
                </div>
                <h1 class="hero-title">Encontre a vaga<br><em>certa para você</em></h1>
                <p class="hero-desc">Conectamos profissionais talentosos às empresas e oportunidades disponíveis em diversas áreas.</p>

                <form class="search-bar" action="/vagas/index.php" method="GET">
                    <input id="busca-pagina-inicial" type="search" name="q" value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>" placeholder="Cargo, empresa, habilidade ou palavra-chave..." aria-label="Buscar vagas" autocomplete="on">
                    <button type="submit" aria-label="Pesquisar vagas">Buscar Vagas</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Stats Bar -->
    <div class="stats-bar">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value"><?= number_format((int) $estatisticas['vagas_ativas'], 0, ',', '.') ?></div>
                <div class="stat-label">Vagas ativas</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= number_format((int) $estatisticas['empresas_cadastradas'], 0, ',', '.') ?></div>
                <div class="stat-label">Empresas cadastradas</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= number_format((int) $estatisticas['candidatos_cadastrados'], 0, ',', '.') ?></div>
                <div class="stat-label">Candidatos registrados</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= $taxaResposta === null ? '—' : $taxaResposta . '%' ?></div>
                <div class="stat-label">Candidaturas respondidas</div>
            </div>
        </div>
    </div>

    <!-- Featured Jobs -->
    <section class="section-container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">§ 01 — Destaques</span>
                <h2 class="section-title">Vagas em destaque</h2>
            </div>
            <div class="filter-tags">
                <a class="filter-tag <?= $areaSelecionada === '' ? 'active' : '' ?>" href="/index.php">Todos</a>
                <?php foreach ($areasDestaque as $area): ?>
                    <a class="filter-tag <?= $areaSelecionada === $area ? 'active' : '' ?>" href="/index.php?area=<?= urlencode($area) ?>"><?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="job-grid">
            <?php if (!$vagasDestaque): ?>
                <div class="job-card">
                    <h3>Nenhuma vaga encontrada</h3>
                    <p><?= $areaSelecionada === '' ? 'Ainda não há vagas ativas.' : 'Não há vagas ativas nesta área.' ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($vagasDestaque as $vaga): ?>
                    <a href="/vagas/detalhes.php?id=<?= (int) $vaga['id'] ?>" class="job-card"
                        style="display: block; text-decoration: none; color: inherit;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <div>
                                <div style="font-family: 'Fraunces', serif; font-size: 15px; font-weight: 600; margin-bottom: 2px;">
                                    <?= htmlspecialchars($vaga['titulo'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div style="font-size: 12px; color: var(--muted-foreground);">
                                    <?= htmlspecialchars($vaga['empresa_nome'], ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                            <div class="company-logo" style="width: 36px; height: 36px; font-size: 14px;">
                                <?= htmlspecialchars(strtoupper(substr($vaga['empresa_nome'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div style="font-size: 12px; color: var(--muted-foreground); margin-bottom: 12px;">
                            <?= htmlspecialchars($vaga['localizacao'] ?? 'Localização não informada', ENT_QUOTES, 'UTF-8') ?> ·
                            <?= htmlspecialchars($vaga['regime'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 13px; font-weight: 600; color: var(--primary);">
                                <?php if ($vaga['exibir_salario'] && $vaga['salario_min'] !== null): ?>
                                    R$ <?= number_format((float) $vaga['salario_min'], 2, ',', '.') ?><?= $vaga['salario_max'] !== null ? ' – R$ ' . number_format((float) $vaga['salario_max'], 2, ',', '.') : '' ?>
                                <?php else: ?>
                                    A combinar
                                <?php endif; ?>
                            </span>
                            <span style="font-size: 12px; color: var(--accent); font-weight: 600;">Ver vaga →</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="text-align: center;">
            <a href="vagas/index.php" class="btn-outline">Ver todas as vagas →</a>
        </div>
    </section>

    <!-- Divider -->
    <div style="height: 1px; background-color: var(--border); max-width: 1200px; margin: 0 auto;"></div>

    <!-- Companies Section -->
    <section class="section-container">
        <span class="section-eyebrow" style="margin-bottom: 6px;">§ 02 — Empresas</span>
        <div class="section-header" style="margin-bottom: 32px;">
            <h2 class="section-title" style="margin: 0;">Empresas que confiam em nós</h2>
            <a href="empresa/cadastro.php"
                style="font-size: 13px; color: var(--accent); font-weight: 600; font-family: 'Work Sans', sans-serif;">Cadastrar
                minha empresa →</a>
        </div>

        <div class="companies-grid">
            <?php if (!$empresasDestaque): ?>
                <p>Nenhuma empresa tem vagas ativas no momento.</p>
            <?php else: ?>
                <?php foreach ($empresasDestaque as $empresa): ?>
                    <div class="company-card">
                        <div class="company-logo"><?= htmlspecialchars(strtoupper(substr($empresa['nome'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
                        <div>
                            <div style="font-weight: 600; font-size: 15px;"><?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div style="font-size: 12px; color: var(--muted-foreground);"><?= htmlspecialchars($empresa['setor'] ?: 'Empresa cadastrada', ENT_QUOTES, 'UTF-8') ?></div>
                            <div style="font-size: 12px; color: var(--accent); font-weight: 600; margin-top: 2px;">
                                <?= (int) $empresa['vagas_ativas'] ?> vaga<?= (int) $empresa['vagas_ativas'] === 1 ? '' : 's' ?> aberta<?= (int) $empresa['vagas_ativas'] === 1 ? '' : 's' ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- How it works -->
    <section class="how-it-works">
        <div style="max-width: 1200px; margin: 0 auto;">
            <span
                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--accent); display: block; margin-bottom: 6px;">
                § 03 — Como funciona
            </span>
            <h2
                style="font-family: 'Fraunces', serif; font-size: 30px; font-weight: 600; color: #F2EDE5; margin: 0 0 48px; letter-spacing: -0.5px;">
                Simples para empresas e candidatos
            </h2>

            <div class="how-grid">
                <!-- For candidates -->
                <div>
                    <div class="step-col-title">Para candidatos</div>

                    <div class="step-item">
                        <div class="step-number">01</div>
                        <div class="step-text">
                            <h4>Crie seu perfil</h4>
                            <p>Cadastre-se gratuitamente e construa seu perfil profissional com currículo e portfólio.
                            </p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">02</div>
                        <div class="step-text">
                            <h4>Explore vagas</h4>
                            <p>Filtre por área, localização, regime e faixa salarial para encontrar a vaga ideal.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">03</div>
                        <div class="step-text">
                            <h4>Candidate-se</h4>
                            <p>Envie sua candidatura com um clique e acompanhe o status em tempo real.</p>
                        </div>
                    </div>
                </div>

                <!-- For companies -->
                <div style="padding-left: 48px; border-left: 1px solid rgba(242,237,229,0.1);">
                    <div class="step-col-title">Para empresas</div>

                    <div class="step-item">
                        <div class="step-number" style="color: #F2EDE5;">01</div>
                        <div class="step-text">
                            <h4>Registre sua empresa</h4>
                            <p>Cadastre CNPJ, perfil da empresa e informações de contato em poucos minutos.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number" style="color: #F2EDE5;">02</div>
                        <div class="step-text">
                            <h4>Publique vagas</h4>
                            <p>Descreva a vaga, defina requisitos, salário e benefícios. Publique instantaneamente.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number" style="color: #F2EDE5;">03</div>
                        <div class="step-text">
                            <h4>Gerencie candidatos</h4>
                            <p>Receba candidaturas, filtre perfis, agende entrevistas e feche contratações.</p>
                        </div>
                    </div>

                    <a href="/empresa/cadastro.php"
                        style="display: inline-block; padding: 12px 24px; background-color: var(--accent); color: #FAF8F4; font-size: 13px; font-weight: 600; border-radius: 2px; margin-top: 8px; transition: opacity 0.15s;">
                        Começar agora →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div style="display: flex; align-items: baseline; gap: 6px;">
            <span
                style="font-family: 'Fraunces', serif; font-size: 18px; font-weight: 600; color: var(--primary);">Infodin</span>
        </div>
        <p style="font-size: 12px; color: var(--muted-foreground); margin: 0;">
            © 2026 Infodin — Todos os direitos reservados
        </p>
        <div class="footer-links">
            <a href="#">Termos</a>
            <a href="#">Privacidade</a>
            <a href="#">Contato</a>
        </div>
    </footer>

    <script src="/JS/tema.js"></script>

</body>

</html>