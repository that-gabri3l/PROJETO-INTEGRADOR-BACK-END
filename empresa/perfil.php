<?php
require_once __DIR__ . '/../src/conexao.php';

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
    <nav class="navbar">
        <a href="/index.php" class="navbar-logo">
            <img src="/images/logo-claro-infodin.png" alt="Logo do site" class="logo-light">
            <img src="/images/logo-infodin.png" alt="Logo do site" class="logo-dark">
        </a>
        <button id="theme-toggle" class="theme-toggle" type="button" title="Mudar tema">🌗</button>
    </nav>

    <main class="company-profile">
        <section class="company-header">
            <?php if ($empresa['logo']): ?>
                <img class="company-logo-image" src="<?= htmlspecialchars($empresa['logo'], ENT_QUOTES, 'UTF-8') ?>" alt="Logo de <?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?>">
            <?php else: ?>
                <div class="company-logo"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <div>
                <span class="eyebrow">Perfil da empresa</span>
                <h1><?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="company-meta">
                    <?php if ($empresa['setor']): ?>
                        <span><?= htmlspecialchars($empresa['setor'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?php if ($empresa['cidade_sede']): ?>
                        <span><?= htmlspecialchars($empresa['cidade_sede'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?php if ($empresa['porte']): ?>
                        <span><?= htmlspecialchars($empresa['porte'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="company-content">
            <article>
                <div class="section-label">Sobre</div>
                <?php if ($empresa['descricao']): ?>
                    <p class="company-description"><?= nl2br(htmlspecialchars($empresa['descricao'], ENT_QUOTES, 'UTF-8')) ?></p>
                <?php else: ?>
                    <p class="company-description">Esta empresa ainda não adicionou uma descrição.</p>
                <?php endif; ?>
            </article>

            <aside class="company-details">
                <div class="section-label">Informações</div>
                <?php if ($empresa['cidade_sede']): ?>
                    <div class="detail-row"><strong>Localização</strong><span><?= htmlspecialchars($empresa['cidade_sede'], ENT_QUOTES, 'UTF-8') ?></span></div>
                <?php endif; ?>
                <?php if ($empresa['telefone']): ?>
                    <div class="detail-row"><strong>Telefone</strong><span><?= htmlspecialchars($empresa['telefone'], ENT_QUOTES, 'UTF-8') ?></span></div>
                <?php endif; ?>
                <?php if ($empresa['email_corporativo']): ?>
                    <div class="detail-row"><strong>E-mail</strong><span><?= htmlspecialchars($empresa['email_corporativo'], ENT_QUOTES, 'UTF-8') ?></span></div>
                <?php endif; ?>
                <?php if ($site !== '' && filter_var($site, FILTER_VALIDATE_URL)): ?>
                    <div class="detail-row"><strong>Site</strong><a href="<?= htmlspecialchars($site, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Visitar site</a></div>
                <?php endif; ?>
            </aside>
        </section>

        <section class="company-vagas">
            <div class="section-label">Vagas abertas</div>
            <?php if (!$vagas): ?>
                <p class="empty-state">Esta empresa não possui vagas abertas no momento.</p>
            <?php else: ?>
                <div class="jobs-grid">
                    <?php foreach ($vagas as $vaga): ?>
                        <a class="job-card" href="/vagas/detalhes.php?id=<?= (int) $vaga['id'] ?>">
                            <h2><?= htmlspecialchars($vaga['titulo'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <p><?= htmlspecialchars($vaga['area'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($vaga['regime'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p><?= htmlspecialchars($vaga['localizacao'], ENT_QUOTES, 'UTF-8') ?></p>
                            <span class="job-link">Ver vaga →</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script src="/JS/tema.js"></script>
</body>
</html>
