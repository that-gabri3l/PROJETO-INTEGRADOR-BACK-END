<?php
require_once __DIR__ . '/../src/conexao.php';
require_once __DIR__ . '/../src/sessao.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit('Vaga não encontrada.');
}

$stmt = $pdo->prepare(
    'SELECT v.*, e.nome AS empresa_nome, e.setor, e.cidade_sede, e.logo AS empresa_logo
     FROM vagas v
     INNER JOIN empresas e ON e.id = v.empresa_id
     WHERE v.id = :id AND v.status = \'ativa\'
     LIMIT 1'
);
$stmt->execute(['id' => $id]);
$vaga = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vaga) {
    http_response_code(404);
    exit('Vaga não encontrada.');
}

$erro = isset($_GET['erro']) ? trim((string) $_GET['erro']) : '';
$estadoVagaSalva = trim((string) ($_GET['vaga'] ?? ''));
$requisitos = array_filter(array_map('trim', preg_split('/\R/', (string) $vaga['requisitos'])));
$beneficios = array_filter(array_map('trim', preg_split('/\R/', (string) $vaga['beneficios'])));
$vagaSalva = false;
$candidatoLogado = usuarioEstaLogado() && ($_SESSION['usuario_tipo'] ?? null) === 'candidato';
$candidatoId = $_SESSION['perfil_id'] ?? null;

if ($candidatoLogado && $candidatoId) {
    $stmtVagaSalva = $pdo->prepare(
        'SELECT 1 FROM vagas_salvas WHERE candidato_id = :candidato_id AND vaga_id = :vaga_id LIMIT 1'
    );
    $stmtVagaSalva->execute([
        'candidato_id' => $candidatoId,
        'vaga_id' => $vaga['id'],
    ]);
    $vagaSalva = (bool) $stmtVagaSalva->fetchColumn();
}

$logoEmpresa = null;
if (!empty($vaga['empresa_logo'])) {
    $nomeLogo = basename(str_replace('\\', '/', (string) $vaga['empresa_logo']));
    $caminhoLogo = __DIR__ . '/../src/uploads/logos_empresas/' . $nomeLogo;
    if (preg_match('/\A[a-f0-9]{32}\.(?:jpg|png)\z/i', $nomeLogo) && is_file($caminhoLogo)) {
        $logoEmpresa = '/src/uploads/logos_empresas/' . $nomeLogo;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Vaga - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/CSS/detalhes-vaga.css">
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
        <button id="theme-toggle" class="theme-toggle" title="Mudar tema" aria-label="Alternar tema"><svg class="bi bi-moon-fill" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                <path d="M6 .278a.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278" />
            </svg></button>
    </nav>

    <div class="breadcrumb">
        <div class="breadcrumb-inner">
            <nav class="breadcrumb-trail" aria-label="Navegação estrutural">
                <?php if ($candidatoLogado): ?>
                    <a class="breadcrumb-back" href="/candidato/painel.php" aria-label="Voltar ao painel do candidato">
                        <span aria-hidden="true">←</span> Voltar
                    </a>
                    <span class="separator breadcrumb-divider" aria-hidden="true">|</span>
                <?php endif; ?>
                <a href="/index.php">Início</a>
                <span class="separator">›</span>

                <a href="/vagas/index.php">Vagas</a>
                <span class="separator">›</span>
                <span class="current"><?= htmlspecialchars($vaga['titulo'], ENT_QUOTES, 'UTF-8') ?></span>
            </nav>
        </div>
    </div>

    <main class="detail-layout">
        <!-- Conteúdo Principal -->
        <div>
            <!-- Cabeçalho da Vaga -->
            <div style="margin-bottom: 40px;">
                <div class="eyebrow"><?= htmlspecialchars($vaga['area'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($vaga['regime'], ENT_QUOTES, 'UTF-8') ?></div>
                <h1 class="job-title-large"><?= htmlspecialchars($vaga['titulo'], ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="company-name"><?= htmlspecialchars($vaga['empresa_nome'], ENT_QUOTES, 'UTF-8') ?></div>

                <div class="meta-tags">
                    <div class="meta-tag"><span>📍</span> <?= htmlspecialchars($vaga['localizacao'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="meta-tag"><span>⏱</span> <?= htmlspecialchars($vaga['regime'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if ($vaga['exibir_salario'] && $vaga['salario_min'] !== null): ?>
                        <div class="meta-tag"><span>💰</span> R$ <?= number_format((float) $vaga['salario_min'], 2, ',', '.') ?><?= $vaga['salario_max'] !== null ? ' – R$ ' . number_format((float) $vaga['salario_max'], 2, ',', '.') : '' ?></div>
                    <?php else: ?>
                        <div class="meta-tag"><span>💰</span> A combinar</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="divider-large"></div>

            <!-- Descrição -->
            <div class="section-box">
                <div class="section-label">
                    <div class="section-dash"></div>
                    <h2 class="section-title">Sobre a vaga</h2>
                </div>
                <p class="description-text"><?= nl2br(htmlspecialchars($vaga['descricao'], ENT_QUOTES, 'UTF-8')) ?></p>
            </div>

            <!-- Requisitos -->
            <div class="section-box">
                <div class="section-label">
                    <div class="section-dash"></div>
                    <h2 class="section-title">Requisitos</h2>
                </div>
                <ul class="requirements-list">
                    <?php foreach ($requisitos as $requisito): ?>
                        <li><span class="list-bullet"></span> <?= htmlspecialchars($requisito, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Benefícios -->
            <div class="section-box">
                <div class="section-label">
                    <div class="section-dash"></div>
                    <h2 class="section-title">Benefícios</h2>
                </div>
                <div class="benefits-grid">
                    <?php if (!$beneficios): ?>
                        <div class="benefit-item">Consulte os benefícios no anúncio</div>
                    <?php else: ?>
                        <?php foreach ($beneficios as $beneficio): ?>
                            <div class="benefit-item"><span class="check-icon">✓</span> <?= htmlspecialchars($beneficio, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Barra Lateral -->
        <aside>
            <div class="sidebar">
                <!-- Card de Candidatura -->
                <div class="sidebar-card">
                    <div class="eyebrow" style="margin-bottom: 16px;">Candidatura</div>

                    <!-- Formulário para o Back-End -->
                    <form action="/src/processar-candidatura.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="vaga_id" value="<?= (int) $vaga['id'] ?>">
                        <?php if ($erro !== ''): ?>
                            <div class="form-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label class="form-label">Link do Currículo ou Portfólio</label>
                            <input type="url" name="portfolio" class="form-input" placeholder="https://linkedin.com/in/..." required>
                        </div>

                        <button type="submit" class="btn-primary" style="margin-top: 8px;">
                            Me candidatar
                        </button>
                    </form>
                    <form action="/src/vagas-salvas.php" method="POST" style="margin-top:8px">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="vaga_id" value="<?= (int) $vaga['id'] ?>">
                        <input type="hidden" name="origem" value="/vagas/detalhes.php?id=<?= (int) $vaga['id'] ?>">
                        <?php if ($estadoVagaSalva === 'salva'): ?>
                            <div class="form-success" role="status">Vaga salva com sucesso.</div>
                        <?php elseif ($estadoVagaSalva === 'removida'): ?>
                            <div class="form-success" role="status">Vaga removida dos favoritos.</div>
                        <?php elseif ($estadoVagaSalva === 'erro'): ?>
                            <div class="form-error" role="alert">Não foi possível atualizar a vaga salva.</div>
                        <?php endif; ?>
                        <button type="submit" name="acao" value="<?= $vagaSalva ? 'remover' : 'salvar' ?>" class="btn-secondary" style="width:100%">
                            <?= $vagaSalva ? 'Remover vaga salva' : 'Salvar vaga' ?>
                        </button>
                    </form>
                </div>

                <!-- Card da Empresa -->
                <div class="sidebar-card company-card">
                    <div style="flex-shrink: 0;">
                        <?php if ($logoEmpresa): ?>
                            <img class="company-logo company-logo-image" src="<?= htmlspecialchars($logoEmpresa, ENT_QUOTES, 'UTF-8') ?>" alt="Logo de <?= htmlspecialchars($vaga['empresa_nome'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php else: ?>
                            <div class="company-logo" aria-label="<?= htmlspecialchars($vaga['empresa_nome'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars(strtoupper(substr(trim($vaga['empresa_nome']), 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="eyebrow" style="margin-bottom: 4px;">Sobre a empresa</div>
                        <div style="font-weight: 600; font-size: 15px;"><?= htmlspecialchars($vaga['empresa_nome'], ENT_QUOTES, 'UTF-8') ?></div>
                        <a href="/empresa/perfil.php?id=<?= (int) $vaga['empresa_id'] ?>" style="font-size: 12px; color: var(--muted-foreground); text-decoration: none;">Ver perfil completo →</a>
                    </div>
                </div>
            </div>
        </aside>
    </main>

    <script src="/JS/tema.js"></script>

</body>

</html>