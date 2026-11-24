<?php
require_once __DIR__ . '/../src/sessao.php';
exigirTipo('candidato');

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Candidato';
$cidadeUsuario = $_SESSION['cidade'] ?? '';
require_once __DIR__ . '/../src/conexao.php';

$candidatoId = $_SESSION['perfil_id'] ?? null;
$fotoUsuario = null;
$perfil = null;
$candidaturas = [];
$vagasSalvas = [];
$candidaturasTotal = 0;
$entrevistas = 0;
$vagasDisponiveis = 0;

if ($candidatoId) {
    $stmtPerfil = $pdo->prepare('SELECT * FROM candidatos WHERE id = :id LIMIT 1');
    $stmtPerfil->execute(['id' => $candidatoId]);
    $perfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);
    if ($perfil) {
        $nomeUsuario = $perfil['nome'];
        $cidadeUsuario = $perfil['cidade'] ?? '';
        $fotoUsuario = $perfil['foto_perfil'] ?: null;
    }

    $stmt = $pdo->prepare(
        'SELECT c.status, c.criado_em, v.id AS vaga_id, v.titulo, e.nome AS empresa_nome
         FROM candidaturas c
         INNER JOIN vagas v ON v.id = c.vaga_id
         INNER JOIN empresas e ON e.id = v.empresa_id
         WHERE c.candidato_id = :candidato_id
         ORDER BY c.criado_em DESC'
    );
    $stmt->execute(['candidato_id' => $candidatoId]);
    $candidaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $candidaturasTotal = count($candidaturas);
    $entrevistas = count(array_filter($candidaturas, fn($item) => $item['status'] === 'aprovada'));

    $stmt = $pdo->prepare(
        'SELECT v.id, v.titulo, v.localizacao, v.regime, v.salario_min, v.salario_max, v.exibir_salario, e.nome AS empresa_nome
         FROM vagas_salvas vs
         INNER JOIN vagas v ON v.id = vs.vaga_id
         INNER JOIN empresas e ON e.id = v.empresa_id
         WHERE vs.candidato_id = :candidato_id
         ORDER BY vs.criado_em DESC'
    );
    $stmt->execute(['candidato_id' => $candidatoId]);
    $vagasSalvas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$itensPerfil = [
    ['titulo' => 'Dados básicos', 'pontos' => 20, 'concluido' => !empty($perfil['nome']) && !empty($perfil['email']) && !empty($perfil['cidade'])],
    ['titulo' => 'Adicionar telefone', 'pontos' => 5, 'concluido' => !empty($perfil['telefone'])],
    ['titulo' => 'Adicionar foto', 'pontos' => 10, 'concluido' => !empty($perfil['foto_perfil'])],
    ['titulo' => 'Informar cargo e área', 'pontos' => 20, 'concluido' => !empty($perfil['cargo']) && !empty($perfil['area'])],
    ['titulo' => 'Informar experiência', 'pontos' => 10, 'concluido' => !empty($perfil['experiencia'])],
    ['titulo' => 'Informar escolaridade', 'pontos' => 10, 'concluido' => !empty($perfil['escolaridade'])],
    ['titulo' => 'Escolher regime de trabalho', 'pontos' => 10, 'concluido' => !empty($perfil['regime_preferido'])],
    ['titulo' => 'Adicionar LinkedIn', 'pontos' => 5, 'concluido' => !empty($perfil['linkedin'])],
    ['titulo' => 'Preencher resumo profissional', 'pontos' => 10, 'concluido' => !empty($perfil['sobre'])],
];
$percentualPerfil = array_sum(array_map(
    static fn(array $item): int => $item['concluido'] ? $item['pontos'] : 0,
    $itensPerfil
));

$stmtVagasDisponiveis = $pdo->query('SELECT COUNT(*) FROM vagas WHERE status = "ativa"');
$vagasDisponiveis = (int) $stmtVagasDisponiveis->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Candidato - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/CSS/painel-candidato.css">    
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
        <div class="navbar-actions">
            <button id="theme-toggle" class="theme-toggle" type="button" title="Mudar tema" aria-label="Alternar tema"><svg class="bi bi-moon-fill" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/></svg></button>
            <a href="/src/logout.php" class="nav-btn" title="Sair">Sair</a>
        </div>
    </nav>

    <!-- Header do Painel -->
    <header class="dashboard-header">
        <div class="header-content">
            <div class="avatar-large">
                <?php if ($fotoUsuario): ?>
                    <img src="<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <?= htmlspecialchars(strtoupper(substr($nomeUsuario, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </div>
            <div class="header-info">
                <span class="eyebrow">Perfil do candidato</span>
                <h1 class="user-name"><?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="user-meta"><?= $cidadeUsuario !== '' ? htmlspecialchars($cidadeUsuario, ENT_QUOTES, 'UTF-8') : 'Complete seu perfil profissional' ?></div>
                <a class="btn-edit-profile" href="/candidato/editar-perfil.php">Editar perfil</a>
            </div>
            <div class="profile-progress">
                <div class="progress-title">Perfil completo</div>
                <div class="progress-bar-container">
                    <div class="progress-track" role="progressbar" aria-label="Completude do perfil" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $percentualPerfil ?>">
                        <div class="progress-fill" style="width: <?= $percentualPerfil ?>%;"></div>
                    </div>
                    <span class="progress-percentage"><?= $percentualPerfil ?>%</span>
                </div>
                <div style="font-size: 11px; color: rgba(242,237,229,0.7); margin-top: 6px;">
                    <?= $percentualPerfil === 100 ? 'Seu perfil está completo.' : 'Complete os itens indicados abaixo.' ?>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        
        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-value"><?= $candidaturasTotal ?></div>
                <div class="kpi-label">Candidaturas enviadas</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value"><?= $entrevistas ?></div>
                <div class="kpi-label">Candidaturas aprovadas</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value"><?= count($vagasSalvas) ?></div>
                <div class="kpi-label">Vagas salvas</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value"><?= $vagasDisponiveis ?></div>
                <div class="kpi-label">Vagas disponíveis</div>
            </div>
        </div>

        <div class="content-grid">
            
            <!-- Minhas Candidaturas -->
            <div>
                <div class="section-header">
                    <span class="section-eyebrow">§ 01</span>
                    <h2 class="section-title">Minhas candidaturas</h2>
                </div>
                <div class="list-container">
                    <?php foreach ($candidaturas as $candidatura): ?>
                    <div class="list-item">
                        <div class="company-logo" style="background-color: #6B21A8;">N</div>
                        <div class="item-details">
                            <div class="item-title"><?= htmlspecialchars($candidatura['titulo'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="item-meta"><?= htmlspecialchars($candidatura['empresa_nome'], ENT_QUOTES, 'UTF-8') ?> · <?= date('d/m/Y', strtotime($candidatura['criado_em'])) ?></div>
                        </div>
                        <span class="badge badge-yellow"><?= ucfirst(htmlspecialchars($candidatura['status'], ENT_QUOTES, 'UTF-8')) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (!$candidaturas): ?>
                        <div class="list-item"><div class="item-details"><div class="item-title">Nenhuma candidatura enviada</div><div class="item-meta">Explore vagas e candidate-se ao seu perfil.</div></div></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Coluna da Direita (Vagas Salvas e Checklist) -->
            <div style="display: flex; flex-direction: column; gap: 28px;">
                
                <!-- Vagas Salvas -->
                <div>
                    <div class="section-header">
                        <span class="section-eyebrow">§ 02</span>
                        <h2 class="section-title">Vagas salvas</h2>
                    </div>
                    <div class="list-container">
                        <?php foreach ($vagasSalvas as $vaga): ?>
                        <a class="list-item saved-job-link" href="/vagas/detalhes.php?id=<?= (int) $vaga['id'] ?>" aria-label="Ver detalhes de <?= htmlspecialchars($vaga['titulo'], ENT_QUOTES, 'UTF-8') ?>">
                            <div class="item-details">
                                <div class="item-title"><?= htmlspecialchars($vaga['titulo'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="item-meta"><?= htmlspecialchars($vaga['empresa_nome'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($vaga['localizacao'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($vaga['regime'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div style="font-size: 12px; color: var(--accent); font-weight: 600; margin-top: 2px;">
                                    R$ <?= number_format($vaga['salario_min'], 0, ',', '.') ?> – R$ <?= number_format($vaga['salario_max'], 0, ',', '.') ?>
                                </div>
                            </div>
                            <span aria-hidden="true" style="font-size: 14px; color: var(--muted-foreground);">→</span>
                        </a>
                        <?php endforeach; ?>
                        <?php if (!$vagasSalvas): ?>
                            <div class="list-item"><div class="item-details"><div class="item-title">Nenhuma vaga salva</div><div class="item-meta">Use o botão salvar na página de vagas.</div></div></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Complete seu perfil -->
                <div>
                    <div class="section-header">
                        <span class="section-eyebrow">§ 03</span>
                        <h2 class="section-title">Complete seu perfil</h2>
                    </div>
                    <div class="list-container">
                        <?php foreach ($itensPerfil as $item): ?>
                            <div class="checklist-item <?= $item['concluido'] ? 'checklist-done' : 'checklist-pending' ?>">
                                <div class="checklist-circle">
                                    <?php if ($item['concluido']): ?><span class="check-mark">✓</span><?php endif; ?>
                                </div>
                                <?php if ($item['concluido']): ?>
                                    <span class="checklist-label"><?= htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="checklist-points">Concluído</span>
                                <?php else: ?>
                                    <a class="checklist-label" href="/candidato/editar-perfil.php"><?= htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8') ?></a>
                                    <a class="checklist-points checklist-edit-link" href="/candidato/editar-perfil.php">+<?= $item['pontos'] ?>%</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vagas Recomendadas -->
        <div style="margin-top: 36px;">
            <div class="section-header">
                <h2 class="section-title">Vagas recomendadas para você</h2>
            </div>
            
            <div class="rec-grid">
                <!-- Vaga Recomendada 1 -->
                <div class="rec-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <div style="font-family: 'Fraunces', serif; font-size: 15px; font-weight: 600; margin-bottom: 2px;">Desenvolvedor Full Stack</div>
                            <div style="font-size: 12px; color: var(--muted-foreground);">Acme Corp</div>
                        </div>
                        <div class="company-logo" style="background-color: var(--primary);">A</div>
                    </div>
                    <div style="font-size: 12px; color: var(--muted-foreground); margin-bottom: 12px;">São Paulo, SP · Híbrido</div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; font-weight: 600; color: var(--primary);">R$ 8.000</span>
                        <span style="font-size: 12px; color: var(--accent); font-weight: 600;">Ver vaga →</span>
                    </div>
                </div>

                <!-- Vaga Recomendada 2 -->
                <div class="rec-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <div style="font-family: 'Fraunces', serif; font-size: 15px; font-weight: 600; margin-bottom: 2px;">Engenheiro de Dados</div>
                            <div style="font-size: 12px; color: var(--muted-foreground);">Tech Start</div>
                        </div>
                        <div class="company-logo" style="background-color: var(--primary);">T</div>
                    </div>
                    <div style="font-size: 12px; color: var(--muted-foreground); margin-bottom: 12px;">Remoto · Integral</div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; font-weight: 600; color: var(--primary);">R$ 11.500</span>
                        <span style="font-size: 12px; color: var(--accent); font-weight: 600;">Ver vaga →</span>
                    </div>
                </div>

                <!-- Vaga Recomendada 3 -->
                <div class="rec-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <div style="font-family: 'Fraunces', serif; font-size: 15px; font-weight: 600; margin-bottom: 2px;">UX/UI Designer</div>
                            <div style="font-size: 12px; color: var(--muted-foreground);">Creative Agency</div>
                        </div>
                        <div class="company-logo" style="background-color: var(--primary);">C</div>
                    </div>
                    <div style="font-size: 12px; color: var(--muted-foreground); margin-bottom: 12px;">Rio de Janeiro, RJ · Presencial</div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; font-weight: 600; color: var(--primary);">R$ 6.200</span>
                        <span style="font-size: 12px; color: var(--accent); font-weight: 600;">Ver vaga →</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

        <script src="/JS/tema.js"></script>

</body>
</html>