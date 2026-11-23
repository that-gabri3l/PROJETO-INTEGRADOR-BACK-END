<?php
require_once __DIR__ . '/../src/sessao.php';
exigirTipo('empresa');

$nomeEmpresa = $_SESSION['usuario_nome'] ?? 'Empresa';
require_once __DIR__ . '/../src/conexao.php';

$empresaId = $_SESSION['perfil_id'] ?? null;
$logoEmpresa = null;
$vagas = [];
$candidatosRecentes = [];
$totalVagas = 0;
$totalCandidatos = 0;

if ($empresaId) {
    $stmtEmpresa = $pdo->prepare('SELECT nome, logo FROM empresas WHERE id = :id LIMIT 1');
    $stmtEmpresa->execute(['id' => $empresaId]);
    $perfilEmpresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);
    if ($perfilEmpresa) {
        $nomeEmpresa = $perfilEmpresa['nome'];
        $logoEmpresa = $perfilEmpresa['logo'];
        $_SESSION['usuario_nome'] = $nomeEmpresa;
    }

    $stmt = $pdo->prepare(
        'SELECT v.*, COUNT(c.id) AS total_candidatos
         FROM vagas v
         LEFT JOIN candidaturas c ON c.vaga_id = v.id
         WHERE v.empresa_id = :empresa_id
         GROUP BY v.id
         ORDER BY v.criado_em DESC'
    );
    $stmt->execute(['empresa_id' => $empresaId]);
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalVagas = count($vagas);

    $stmt = $pdo->prepare(
        'SELECT c.status, c.criado_em, ca.nome AS candidato_nome, ca.cargo, v.titulo AS vaga_titulo
         FROM candidaturas c
         INNER JOIN candidatos ca ON ca.id = c.candidato_id
         INNER JOIN vagas v ON v.id = c.vaga_id
         WHERE v.empresa_id = :empresa_id
         ORDER BY c.criado_em DESC
         LIMIT 10'
    );
    $stmt->execute(['empresa_id' => $empresaId]);
    $candidatosRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalCandidatos = count($candidatosRecentes);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel da Empresa - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/CSS/painel-empresa.css"> 
    <link rel="stylesheet" href="/CSS/mobile.css">
    

    
</head>
<body>

    <nav class="navbar">
        <a href="/index.php" class="navbar-logo"><!-- Imagem para o Modo Claro -->
            <img src="/images/logo-claro-infodin.png" alt="Logo do site" class="logo-light"
                style="height:40px; width: auto; object-fit: contain;">

            <!-- Imagem para o Modo Escuro -->
            <img src="/images/logo-infodin.png" alt="Logo do site" class="logo-dark"
                style="height: 40px; width: auto; object-fit: contain;">
                </a>
        <div class="navbar-actions">
            <button id="theme-toggle" class="theme-toggle" type="button" title="Mudar tema">🌗</button>
            <a href="/src/logout.php" class="nav-btn" title="Sair">Sair</a>
        </div>
    </nav>

    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <div class="header-content">
            <div class="company-profile-heading">
                <?php if ($logoEmpresa): ?>
                    <img class="company-profile-logo" src="<?= htmlspecialchars($logoEmpresa, ENT_QUOTES, 'UTF-8') ?>" alt="Logo de <?= htmlspecialchars($nomeEmpresa, ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>
                <div>
                    <span class="eyebrow">Painel da empresa</span>
                    <h1 class="company-title"><?= htmlspecialchars($nomeEmpresa, ENT_QUOTES, 'UTF-8') ?></h1>
                    <a class="btn-edit-company" href="/empresa/editar-perfil.php">Editar perfil</a>
                </div>
            </div>
            <a href="/vagas/publicar.php" class="btn-publish">+ Publicar nova vaga</a>
        </div>
    </header>

    <main class="container">
        
        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-value"><?= $totalVagas ?></div>
                <div class="kpi-label">Vagas publicadas</div>
                <div class="kpi-delta">Dados atualizados agora</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value"><?= $totalCandidatos ?></div>
                <div class="kpi-label">Total de candidatos</div>
                <div class="kpi-delta">Candidaturas recentes</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value">—</div>
                <div class="kpi-label">Visualizações totais</div>
                <div class="kpi-delta">Disponível após métricas</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value">—</div>
                <div class="kpi-label">Taxa de conversão</div>
                <div class="kpi-delta">Disponível após métricas</div>
            </div>
        </div>

        <!-- Tabelas -->
        <div class="tables-grid">
            
            <!-- Tabela: Suas Vagas -->
            <div>
                <div class="table-section-header">
                    <div>
                        <span class="section-eyebrow">§ 01</span>
                        <h2 class="section-title">Suas vagas</h2>
                    </div>
                    <a href="/vagas/publicar.php" class="btn-text">+ Nova vaga</a>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Vaga</th>
                                <th class="col-center">Candidatos</th>
                                <th class="col-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vagas as $vaga): ?>
                            <tr>
                                <td class="text-bold">
                                    <div><?= htmlspecialchars($vaga['titulo'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-meta"><?= date('d/m/Y', strtotime($vaga['criado_em'])) ?></div>
                                </td>
                                <td class="col-center"><span class="large-number"><?= htmlspecialchars($vaga['total_candidatos'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="col-center">
                                    <?php if ($vaga['status'] == 'ativa'): ?>
                                        <span class="badge badge-green">Ativa</span>
                                    <?php elseif ($vaga['status'] == 'pausada'): ?>
                                        <span class="badge badge-yellow">Pausada</span>
                                    <?php else: ?>
                                        <span class="badge badge-red">Encerrada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (!$vagas): ?>
                            <tr><td colspan="3">Nenhuma vaga publicada ainda.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabela: Candidatos Recentes -->
            <div>
                <div class="table-section-header" style="margin-bottom: 16px; display: block;">
                    <span class="section-eyebrow">§ 02</span>
                    <h2 class="section-title">Candidatos recentes</h2>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Candidato</th>
                                <th class="col-right">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidatosRecentes as $candidato): ?>
                            <tr>
                                <td>
                                    <div class="candidate-info">
                                        <div class="avatar"><?= htmlspecialchars($candidato['candidato_nome'][0], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div>
                                            <div class="text-bold"><?= htmlspecialchars($candidato['candidato_nome'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="text-meta" style="font-family: 'Work Sans', sans-serif;"><?= htmlspecialchars($candidato['cargo'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="col-right">
                                    <?php if ($candidato['status'] === 'recebida'): ?>
                                        <span class="badge badge-blue">Nova</span>
                                    <?php elseif ($candidato['status'] === 'em_analise'): ?>
                                        <span class="badge badge-yellow">Em análise</span>
                                    <?php elseif ($candidato['status'] === 'aprovada'): ?>
                                        <span class="badge badge-green">Aprovada</span>
                                    <?php else: ?>
                                        <span class="badge badge-red">Recusada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (!$candidatosRecentes): ?>
                            <tr><td colspan="2">Nenhum candidato recebeu uma vaga ainda.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

        <script src="/JS/tema.js"></script>


</body>
</html>