<?php
require_once __DIR__ . '/../src/sessao.php';
require_once __DIR__ . '/../src/conexao.php';

$usuarioLogado = usuarioEstaLogado();
$usuarioTipo = $_SESSION['usuario_tipo'] ?? null;
$usuarioNome = $_SESSION['usuario_nome'] ?? '';

$q = trim($_GET['q'] ?? '');
$area = trim($_GET['area'] ?? '');
$regime = trim($_GET['regime'] ?? '');
$ordem = $_GET['ordem'] ?? 'recente';

$areasDisponiveis = $pdo->query(
    "SELECT DISTINCT area FROM vagas WHERE status = 'ativa' AND area IS NOT NULL AND area <> '' ORDER BY area"
)->fetchAll(PDO::FETCH_COLUMN);
$regimesDisponiveis = $pdo->query(
    "SELECT DISTINCT regime FROM vagas WHERE status = 'ativa' AND regime IS NOT NULL AND regime <> '' ORDER BY regime"
)->fetchAll(PDO::FETCH_COLUMN);

$where = ['v.status = :status'];
$params = ['status' => 'ativa'];

if ($q !== '') {
    $where[] = '(v.titulo LIKE :q OR v.area LIKE :q OR v.localizacao LIKE :q OR e.nome LIKE :q)';
    $params['q'] = '%' . $q . '%';
}
if (in_array($area, $areasDisponiveis, true)) {
    $where[] = 'v.area = :area';
    $params['area'] = $area;
} else {
    $area = '';
}
if (in_array($regime, $regimesDisponiveis, true)) {
    $where[] = 'v.regime = :regime';
    $params['regime'] = $regime;
} else {
    $regime = '';
}

$orderBy = 'v.criado_em DESC';
if ($ordem === 'salario') {
    $orderBy = 'CASE WHEN v.exibir_salario = 1 THEN COALESCE(v.salario_max, v.salario_min) ELSE NULL END DESC, v.criado_em DESC';
} else {
    $ordem = 'recente';
}

$urlComFiltros = static function (array $alteracoes) use ($q, $area, $regime, $ordem): string {
    $filtros = array_merge([
        'q' => $q,
        'area' => $area,
        'regime' => $regime,
        'ordem' => $ordem,
    ], $alteracoes);
    $filtros = array_filter($filtros, static fn($valor): bool => $valor !== null && $valor !== '');

    return '/vagas/index.php?' . http_build_query($filtros);
};

$sql = 'SELECT v.*, e.nome AS empresa_nome
        FROM vagas v
        INNER JOIN empresas e ON e.id = v.empresa_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY ' . $orderBy;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vagas - Infodin</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
<link rel="stylesheet" href="/CSS/vagas.css"> 
<link rel="stylesheet" href="/CSS/mobile.css">

</head>

<body>

    <nav class="navbar navbar--menu">
        <a href="/index.php" class="navbar-logo"><!-- Imagem para o Modo Claro -->
            <img src="/images/logo-claro-infodin.png" alt="Logo do site" class="logo-light"
                style="height:40px; width: auto; object-fit: contain;">

            <!-- Imagem para o Modo Escuro -->
            <img src="/images/logo-infodin.png" alt="Logo do site" class="logo-dark"
                style="height: 40px; width: auto; object-fit: contain;"></a>

        <!-- Checkbox invisível que controla o menu -->
        <input type="checkbox" id="menu-toggle" class="menu-toggle">

        <!-- Ícone do Hambúrguer (associado ao checkbox) -->
        <label for="menu-toggle" class="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </label>


        <div class="menu-links">
            <?php if (!$usuarioLogado): ?>
                <a href="/empresa/cadastro.php" class="nav-link">Para Empresas</a>
                <a href="/cadastro/escolha.php" class="nav-link">Cadastrar</a>
                <a href="/cadastro/login.php" class="nav-btn">Entrar</a>
            <?php else: ?>
                <a href="<?= $usuarioTipo === 'empresa' ? '/empresa/painel.php' : '/candidato/painel.php' ?>" class="nav-btn">
                    <?= htmlspecialchars($usuarioNome !== '' ? $usuarioNome : 'Meu painel', ENT_QUOTES, 'UTF-8') ?>
                </a>
                <a href="/src/logout.php" class="nav-link">Sair</a>
            <?php endif; ?>
            <button id="theme-toggle" class="theme-toggle" title="Mudar tema" aria-label="Alternar tema"><svg class="bi bi-moon-fill" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/></svg></button>
        </div>
    </nav>

    <div class="page-header">
        <div class="header-container">
            <span class="eyebrow-text">Explorar vagas</span>
            <h1 class="page-title"><?= count($vagas) ?> <?= count($vagas) === 1 ? 'vaga disponível' : 'vagas disponíveis' ?></h1>

            <form class="search-bar" action="/vagas/index.php" method="GET">
                <input type="text" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Buscar por cargo, empresa ou habilidade...">
                <?php if ($area !== ''): ?><input type="hidden" name="area" value="<?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <?php if ($regime !== ''): ?><input type="hidden" name="regime" value="<?= htmlspecialchars($regime, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <?php if ($ordem !== 'recente'): ?><input type="hidden" name="ordem" value="<?= htmlspecialchars($ordem, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <button type="submit">Buscar</button>
            </form>
        </div>
    </div>

    <div class="main-layout">
        <!-- Sidebar Filters -->
        <aside class="sidebar">
            <div class="filter-section">
                <div class="filter-title">Área</div>
                <div class="filter-list">
                    <a href="<?= htmlspecialchars($urlComFiltros(['area' => null]), ENT_QUOTES, 'UTF-8') ?>" class="filter-btn <?= $area === '' ? 'active-primary' : '' ?>">Todas</a>
                    <?php foreach ($areasDisponiveis as $areaDisponivel): ?>
                        <a href="<?= htmlspecialchars($urlComFiltros(['area' => $areaDisponivel]), ENT_QUOTES, 'UTF-8') ?>" class="filter-btn <?= $area === $areaDisponivel ? 'active-primary' : '' ?>"><?= htmlspecialchars($areaDisponivel, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="divider"></div>

            <div class="filter-section">
                <div class="filter-title">Regime</div>
                <div class="filter-list">
                    <a href="<?= htmlspecialchars($urlComFiltros(['regime' => null]), ENT_QUOTES, 'UTF-8') ?>" class="filter-btn <?= $regime === '' ? 'active-primary' : '' ?>">Todos</a>
                    <?php foreach ($regimesDisponiveis as $regimeDisponivel): ?>
                        <a href="<?= htmlspecialchars($urlComFiltros(['regime' => $regimeDisponivel]), ENT_QUOTES, 'UTF-8') ?>" class="filter-btn <?= $regime === $regimeDisponivel ? 'active-primary' : '' ?>"><?= htmlspecialchars($regimeDisponivel, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="divider"></div>

            <div class="filter-section">
                <div class="filter-title">Ordenar por</div>
                <div class="filter-list">
                    <a href="<?= htmlspecialchars($urlComFiltros(['ordem' => 'recente']), ENT_QUOTES, 'UTF-8') ?>" class="filter-btn <?= $ordem === 'recente' ? 'active-secondary' : '' ?>">Mais recente</a>
                    <a href="<?= htmlspecialchars($urlComFiltros(['ordem' => 'salario']), ENT_QUOTES, 'UTF-8') ?>" class="filter-btn <?= $ordem === 'salario' ? 'active-secondary' : '' ?>">Maior salário</a>
                </div>
            </div>
        </aside>

        <!-- Job List -->
        <div class="jobs-grid">
            <?php if (!$vagas): ?>
                <div class="job-card">
                    <h3 class="job-title">Nenhuma vaga encontrada</h3>
                    <div class="job-meta">Tente alterar os filtros ou buscar outro termo.</div>
                    <div class="job-footer"><a href="/vagas/index.php" class="btn-apply">Limpar filtros</a></div>
                </div>
            <?php else: ?>
                <?php foreach ($vagas as $vaga): ?>
                    <div class="job-card">
                        <div class="job-company"><?= htmlspecialchars($vaga['empresa_nome'], ENT_QUOTES, 'UTF-8') ?></div>
                        <h3 class="job-title"><?= htmlspecialchars($vaga['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <div class="job-meta"><?= htmlspecialchars($vaga['area'], ENT_QUOTES, 'UTF-8') ?> • <?= htmlspecialchars($vaga['regime'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="job-footer">
                            <span class="job-salary">
                                <?php if ($vaga['exibir_salario'] && $vaga['salario_min'] !== null): ?>
                                    R$ <?= number_format((float) $vaga['salario_min'], 2, ',', '.') ?>
                                    <?= $vaga['salario_max'] !== null ? ' – R$ ' . number_format((float) $vaga['salario_max'], 2, ',', '.') : '' ?>
                                <?php else: ?>
                                    A combinar
                                <?php endif; ?>
                            </span>
                            <form action="/src/vagas-salvas.php" method="POST" style="display:flex;gap:8px">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="vaga_id" value="<?= (int) $vaga['id'] ?>">
                                <input type="hidden" name="origem" value="/vagas/index.php<?= htmlspecialchars('?' . ($_SERVER['QUERY_STRING'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" name="acao" value="salvar" class="btn-apply">Salvar</button>
                                <a href="/vagas/detalhes.php?id=<?= (int) $vaga['id'] ?>" class="btn-apply">Ver vaga</a>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

        <script src="/JS/tema.js"></script>


</body>

</html>