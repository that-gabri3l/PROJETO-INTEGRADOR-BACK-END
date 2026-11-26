<?php
require_once __DIR__ . '/../src/conexao.php';
require_once __DIR__ . '/../src/sessao.php';

$usuarioLogado = usuarioEstaLogado();
$usuarioTipo = $_SESSION['usuario_tipo'] ?? null;
$usuarioNome = $_SESSION['usuario_nome'] ?? '';
$backUrl = $usuarioTipo === 'candidato' ? '/candidato/painel.php' : ($usuarioTipo === 'empresa' ? '/empresa/painel.php' : '/vagas/index.php');
$backLabel = $usuarioTipo === 'candidato' ? 'Voltar ao painel' : ($usuarioTipo === 'empresa' ? 'Voltar ao painel' : 'Voltar às vagas');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit('Empresa não encontrada.');
}

$stmt = $pdo->prepare(
    'SELECT id, nome, razao_social, cnpj, setor, porte, email_corporativo,
            telefone, site, cidade_sede, descricao, logo
     FROM empresas
     WHERE id = :id
     LIMIT 1'
);
$stmt->execute(['id' => $id]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {
    http_response_code(404);
    exit('Empresa não encontrada.');
}

$stmtVagas = $pdo->prepare(
    'SELECT id, titulo, area, regime, localizacao, salario_min, salario_max, exibir_salario
     FROM vagas
     WHERE empresa_id = :empresa_id AND status = :status
     ORDER BY criado_em DESC'
);
$stmtVagas->execute(['empresa_id' => $id, 'status' => 'ativa']);
$vagas = $stmtVagas->fetchAll(PDO::FETCH_ASSOC);

$iniciais = strtoupper(substr(trim($empresa['nome']), 0, 1));
$site = trim((string) $empresa['site']);
if ($site !== '' && !preg_match('#^https?://#i', $site)) {
    $site = 'https://' . $site;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?> - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/CSS/perfil-empresa.css">
    <link rel="stylesheet" href="/CSS/mobile.css">
</head>
<body>
    <nav class="navbar navbar--menu">
        <a href="/index.php" class="navbar-logo">
            <img src="/images/logo-claro-infodin.png" alt="Logo do site" class="logo-light">
            <img src="/images/logo-infodin.png" alt="Logo do site" class="logo-dark">
        </a>

        <input type="checkbox" id="menu-toggle" class="menu-toggle">
        <label for="menu-toggle" class="hamburger" aria-label="Abrir menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </label>

        <div class="menu-links">
            <a href="/vagas/index.php" class="nav-link">Buscar Vagas</a>
            <?php if (!$usuarioLogado): ?>
                <a href="/empresa/cadastro.php" class="nav-link">Para Empresas</a>
                <a href="/candidato/cadastro.php" class="nav-link">Cadastrar</a>
                <a href="/cadastro/login.php" class="nav-btn">Entrar</a>
            <?php else: ?>
                <a href="<?= $usuarioTipo === 'empresa' ? '/empresa/painel.php' : '/candidato/painel.php' ?>" class="nav-btn"><?= htmlspecialchars($usuarioNome !== '' ? $usuarioNome : 'Meu painel', ENT_QUOTES, 'UTF-8') ?></a>
                <a href="/src/logout.php" class="nav-link">Sair</a>
            <?php endif; ?>
            <button id="theme-toggle" class="theme-toggle" type="button" title="Mudar tema" aria-label="Alternar tema"><svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/></svg></button>
        </div>
    </nav>

    <main class="company-profile">
        <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>" class="back-link">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg>
            <?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8') ?>
        </a>

        <header class="company-header">
            <?php if ($empresa['logo']): ?>
                <img class="company-logo-image" src="<?= htmlspecialchars($empresa['logo'], ENT_QUOTES, 'UTF-8') ?>" alt="Logo de <?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?>">
            <?php else: ?>
                <div class="company-logo"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <div class="company-heading">
                <span class="eyebrow">Perfil da empresa</span>
                <h1><?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="company-meta">
                    <?php if ($empresa['setor']): ?>
                        <span>
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v8A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1zM6 3v-.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V3z"/></svg>
                            <?= htmlspecialchars($empresa['setor'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($empresa['cidade_sede']): ?>
                        <span>
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/></svg>
                            <?= htmlspecialchars($empresa['cidade_sede'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($empresa['porte']): ?>
                        <span>
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M7 14s-1 0-1-1 1-4 1-4H4s-1 0-1-1 1-4 1-4h8s1 0 1 1-1 4-1 4h-3s-1 0-1 1 1 4 1 4z"/></svg>
                            <?= htmlspecialchars($empresa['porte'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <section class="company-content">
            <article class="profile-card">
                <div class="section-header">
                    <span class="section-eyebrow">Perfil empresarial</span>
                    <h2 class="section-title">Sobre a empresa</h2>
                </div>
                <?php if ($empresa['descricao']): ?>
                    <p class="company-description"><?= nl2br(htmlspecialchars($empresa['descricao'], ENT_QUOTES, 'UTF-8')) ?></p>
                <?php else: ?>
                    <p class="company-description">Esta empresa ainda não adicionou uma descrição.</p>
                <?php endif; ?>
            </article>

            <aside class="profile-card company-details">
                <div class="section-header">
                    <span class="section-eyebrow">Contato</span>
                    <h2 class="section-title">Informações</h2>
                </div>
                <?php if ($empresa['cidade_sede']): ?>
                    <div class="detail-row"><span class="detail-icon"><svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/></svg></span><div><strong>Localização</strong><span><?= htmlspecialchars($empresa['cidade_sede'], ENT_QUOTES, 'UTF-8') ?></span></div></div>
                <?php endif; ?>
                <?php if ($empresa['telefone']): ?>
                    <div class="detail-row"><span class="detail-icon"><svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.257 7.234 17.6 17.6 0 0 0 7.234 4.257c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a.68.68 0 0 1-.64-.17l-3.42-3.42a.68.68 0 0 1-.17-.64l.547-2.19a.68.68 0 0 0-.122-.58z"/></svg></span><div><strong>Telefone</strong><span><?= htmlspecialchars($empresa['telefone'], ENT_QUOTES, 'UTF-8') ?></span></div></div>
                <?php endif; ?>
                <?php if ($empresa['email_corporativo']): ?>
                    <div class="detail-row"><span class="detail-icon"><svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v.217l-8 4.8-8-4.8zm0 1.383v6.384A2 2 0 0 0 2 13.767h12a2 2 0 0 0 2-2V5.383l-7.555 4.533a.5.5 0 0 1-.51 0z"/></svg></span><div><strong>E-mail</strong><a href="mailto:<?= htmlspecialchars($empresa['email_corporativo'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($empresa['email_corporativo'], ENT_QUOTES, 'UTF-8') ?></a></div></div>
                <?php endif; ?>
                <?php if ($site !== '' && filter_var($site, FILTER_VALIDATE_URL)): ?>
                    <div class="detail-row"><span class="detail-icon"><svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m7.5-6.923c-.263.101-.499.22-.712.354A7 7 0 0 0 5.131 3.09c.514-.23 1.09-.408 1.732-.49zM4.152 4.152A6 6 0 0 1 6.5 2.2V6H3.153a6 6 0 0 1 .999-1.848M2.5 7.5H6v1H2.5zm.653 2.5H6v3.8A6 6 0 0 1 3.153 10M7.5 13.8V10H10v3.8a6 6 0 0 1-2.5.0M11.5 10h3.347A6 6 0 0 1 11.5 13.8zm3.5-1h-3.5V7.5H15z"/></svg></span><div><strong>Site</strong><a href="<?= htmlspecialchars($site, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Visitar site</a></div></div>
                <?php endif; ?>
            </aside>
        </section>

        <section class="company-vagas profile-card">
            <div class="section-header">
                <span class="section-eyebrow">Oportunidades</span>
                <h2 class="section-title">Vagas abertas</h2>
            </div>
            <?php if (!$vagas): ?>
                <p class="empty-state">Esta empresa não possui vagas abertas no momento.</p>
            <?php else: ?>
                <div class="jobs-grid">
                    <?php foreach ($vagas as $vaga): ?>
                        <a class="job-card" href="/vagas/detalhes.php?id=<?= (int) $vaga['id'] ?>">
                            <h2><?= htmlspecialchars($vaga['titulo'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <p><?= htmlspecialchars($vaga['area'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($vaga['regime'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p>
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/></svg>
                                <?= htmlspecialchars($vaga['localizacao'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <?php if ($vaga['exibir_salario'] && $vaga['salario_min'] !== null): ?>
                                <p class="job-salary">R$ <?= number_format((float) $vaga['salario_min'], 0, ',', '.') ?><?= $vaga['salario_max'] !== null ? ' – R$ ' . number_format((float) $vaga['salario_max'], 0, ',', '.') : '' ?></p>
                            <?php else: ?>
                                <p class="job-salary">A combinar</p>
                            <?php endif; ?>
                            <span class="job-link">Ver vaga <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/></svg></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script src="/JS/tema.js"></script>
</body>
</html>
