<?php
require_once __DIR__ . '/../src/conexao.php';
require_once __DIR__ . '/../src/sessao.php';

$q = trim($_GET['q'] ?? '');
$area = trim($_GET['area'] ?? '');
$regime = trim($_GET['regime'] ?? '');
$ordem = $_GET['ordem'] ?? 'recente';

$where = ['v.status = :status'];
$params = ['status' => 'ativa'];

if ($q !== '') {
    $where[] = '(v.titulo LIKE :q OR v.area LIKE :q OR v.localizacao LIKE :q OR e.nome LIKE :q)';
    $params['q'] = '%' . $q . '%';
}
if (in_array($area, ['Tecnologia', 'Design', 'Marketing', 'Dados', 'Gestão', 'Vendas', 'Jurídico', 'Financeiro', 'RH'], true)) {
    $where[] = 'v.area = :area';
    $params['area'] = $area;
}
if (in_array($regime, ['Remoto', 'Híbrido', 'Presencial'], true)) {
    $where[] = 'v.regime = :regime';
    $params['regime'] = $regime;
}

$orderBy = 'v.criado_em DESC';
if ($ordem === 'salario') {
    $orderBy = 'v.salario_max DESC';
}

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

</head>

<body>

    <nav class="navbar">
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
            <a href="/empresa/cadastro.php" class="nav-link"
                style="text-decoration: none; color: var(--foreground); font-weight: 500; margin-right: 20px;">Para
                Empresas</a>
            <a href="/candidato/cadastro.php" class="nav-btn"
                style="text-decoration: none; color: var(--primary); font-weight: 600;">Entrar</a>
            <button id="theme-toggle" class="theme-toggle" title="Mudar tema">🌗</button>
        </div>
    </nav>

    <div class="page-header">
        <div class="header-container">
            <span class="eyebrow-text">Explorar vagas</span>
            <h1 class="page-title"><?= count($vagas) ?> vaga<?= count($vagas) === 1 ? '' : 's' ?> disponível<?= count($vagas) === 1 ? '' : 's' ?></h1>

            <form class="search-bar" action="/vagas/index.php" method="GET">
                <input type="text" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Buscar por cargo, empresa ou habilidade...">
                <?php if ($area !== ''): ?><input type="hidden" name="area" value="<?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <?php if ($regime !== ''): ?><input type="hidden" name="regime" value="<?= htmlspecialchars($regime, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <button type="submit">Buscar</button>
            </form>
        </div>
    </div>

    <div class="main-layout">
        <!-- Sidebar Filters -->
        <aside class="sidebar">
            <form action="/vagas/index.php" method="GET">
                <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <div class="filter-section">
                    <div class="filter-title">Área</div>
                    <div class="filter-list">
                        <?php foreach (['' => 'Todos', 'Tecnologia' => 'Tecnologia', 'Design' => 'Design', 'Marketing' => 'Marketing', 'Dados' => 'Dados', 'Gestão' => 'Gestão'] as $valor => $rotulo): ?>
                            <button type="submit" name="area" value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>" class="filter-btn <?= $area === $valor || ($area === '' && $valor === '') ? 'active-primary' : '' ?>"><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="filter-section">
                    <div class="filter-title">Regime</div>
                    <div class="filter-list">
                        <?php foreach (['' => 'Todos', 'Remoto' => 'Remoto', 'Híbrido' => 'Híbrido', 'Presencial' => 'Presencial'] as $valor => $rotulo): ?>
                            <button type="submit" name="regime" value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>" class="filter-btn <?= $regime === $valor || ($regime === '' && $valor === '') ? 'active-primary' : '' ?>"><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="filter-section">
                    <div class="filter-title">Ordenar por</div>
                    <div class="filter-list">
                        <button type="submit" name="ordem" value="recente" class="filter-btn <?= $ordem !== 'salario' ? 'active-secondary' : '' ?>">Mais recente</button>
                        <button type="submit" name="ordem" value="salario" class="filter-btn <?= $ordem === 'salario' ? 'active-secondary' : '' ?>">Maior salário</button>
                    </div>
                </div>
            </form>
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
                                <button type="submit" name="acao" value="salvar" class="btn-secondary">Salvar</button>
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