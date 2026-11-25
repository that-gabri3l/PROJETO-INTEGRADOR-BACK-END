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
$empresa = filter_input(INPUT_GET, 'empresa', FILTER_VALIDATE_INT);
if ($empresa === false) {
    $empresa = null;
}

$areasDisponiveis = $pdo->query(
    "SELECT DISTINCT area FROM vagas WHERE status = 'ativa' AND area IS NOT NULL AND area <> '' ORDER BY area"
)->fetchAll(PDO::FETCH_COLUMN);
$regimesDisponiveis = $pdo->query(
    "SELECT DISTINCT regime FROM vagas WHERE status = 'ativa' AND regime IS NOT NULL AND regime <> '' ORDER BY regime"
)->fetchAll(PDO::FETCH_COLUMN);

$where = ['v.status = :status'];
$params = ['status' => 'ativa'];

if ($q !== '') {
    $where[] = '(v.titulo LIKE :q
                OR v.area LIKE :q
                OR v.localizacao LIKE :q
                OR v.descricao LIKE :q
                OR v.requisitos LIKE :q
                OR v.beneficios LIKE :q
                OR v.regime LIKE :q
                OR e.nome LIKE :q
                OR e.razao_social LIKE :q
                OR e.setor LIKE :q
                OR e.cidade_sede LIKE :q)';
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
if ($empresa !== null) {
    $empresaExiste = $pdo->prepare(
        "SELECT COUNT(*)
        FROM vagas
        WHERE empresa_id = :empresa_id AND status = 'ativa'"
    );
    $empresaExiste->execute(['empresa_id' => $empresa]);
    if ((int) $empresaExiste->fetchColumn() > 0) {
        $where[] = 'v.empresa_id = :empresa';
        $params['empresa'] = $empresa;
    } else {
        $empresa = null;
    }
}

$orderBy = 'v.criado_em DESC';
if ($ordem === 'salario') {
    $orderBy = 'CASE WHEN v.exibir_salario = 1 THEN COALESCE(v.salario_max, v.salario_min) ELSE NULL END DESC, v.criado_em DESC';
} else {
    $ordem = 'recente';
}

$urlComFiltros = static function (array $alteracoes) use ($q, $area, $regime, $ordem, $empresa): string {
    $filtros = array_merge([
        'q' => $q,
        'area' => $area,
        'regime' => $regime,
        'ordem' => $ordem,
        'empresa' => $empresa,
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

$empresasEncontradas = [];
if ($q !== '') {
    $buscaEmpresa = '%' . $q . '%';
    $stmtEmpresas = $pdo->prepare(
        "SELECT e.id, e.nome, e.razao_social, e.setor, e.cidade_sede,
                COUNT(v.id) AS vagas_ativas
         FROM empresas e
         LEFT JOIN vagas v ON v.empresa_id = e.id AND v.status = 'ativa'
         WHERE e.nome LIKE :nome
            OR e.razao_social LIKE :razao_social
            OR e.setor LIKE :setor
            OR e.cidade_sede LIKE :cidade
         GROUP BY e.id, e.nome, e.razao_social, e.setor, e.cidade_sede
         ORDER BY e.nome ASC"
    );
    $stmtEmpresas->execute([
        'nome' => $buscaEmpresa,
        'razao_social' => $buscaEmpresa,
        'setor' => $buscaEmpresa,
        'cidade' => $buscaEmpresa,
    ]);
    $empresasEncontradas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);
}

$totalVagas = count($vagas);
$totalPaginas = $totalVagas > 1 ? 2 : ($totalVagas === 1 ? 1 : 0);
$paginaAtual = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
$paginaAtual = $paginaAtual === false || $paginaAtual === null ? 1 : max(1, min($paginaAtual, max(1, $totalPaginas)));
$vagasPorPagina = $totalPaginas > 0 ? (int) ceil($totalVagas / $totalPaginas) : 0;
$vagas = array_slice($vagas, ($paginaAtual - 1) * $vagasPorPagina, $vagasPorPagina);
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
<link rel="stylesheet" href="/CSS/sugestoes-vagas.css">
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
            <h1 class="page-title"><?= $totalVagas ?> <?= $totalVagas === 1 ? 'vaga disponível' : 'vagas disponíveis' ?></h1>

            <form class="search-bar" action="/vagas/index.php" method="GET">
                <div class="search-autocomplete" data-search-autocomplete data-suggestions-url="/src/sugestoes-vagas.php">
                    <input id="busca-vagas" type="search" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Cargo, empresa, habilidade ou palavra-chave..." aria-label="Buscar vagas" aria-autocomplete="list" aria-controls="sugestoes-busca-vagas" aria-expanded="false" role="combobox" autocomplete="off">
                    <div id="sugestoes-busca-vagas" class="search-suggestions" role="listbox" hidden></div>
                    <span class="search-suggestions-status" data-suggestions-status role="status" aria-live="polite"></span>
                </div>
                <?php if ($area !== ''): ?><input type="hidden" name="area" value="<?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <?php if ($regime !== ''): ?><input type="hidden" name="regime" value="<?= htmlspecialchars($regime, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <?php if ($ordem !== 'recente'): ?><input type="hidden" name="ordem" value="<?= htmlspecialchars($ordem, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <?php if ($empresa !== null): ?><input type="hidden" name="empresa" value="<?= (int) $empresa ?>"><?php endif; ?>
                <button type="submit" aria-label="Pesquisar vagas">Buscar</button>
            </form>
        </div>
    </div>

    <div class="main-layout main-layout--filtered">
        <aside id="filtros-vagas" class="sidebar vaga-filter-sidebar">
            <form class="vaga-filter-form" action="/vagas/index.php" method="GET">
                <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <?php if ($empresa !== null): ?><input type="hidden" name="empresa" value="<?= (int) $empresa ?>"><?php endif; ?>

                <div class="vaga-filter-field">
                    <label for="filtro-area">Área</label>
                    <select id="filtro-area" name="area">
                        <option value="">Todas</option>
                        <?php foreach ($areasDisponiveis as $areaDisponivel): ?>
                            <option value="<?= htmlspecialchars($areaDisponivel, ENT_QUOTES, 'UTF-8') ?>" <?= $area === $areaDisponivel ? 'selected' : '' ?>>
                                <?= htmlspecialchars($areaDisponivel, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="vaga-filter-field">
                    <label for="filtro-regime">Regime</label>
                    <select id="filtro-regime" name="regime">
                        <option value="">Todos</option>
                        <?php foreach ($regimesDisponiveis as $regimeDisponivel): ?>
                            <option value="<?= htmlspecialchars($regimeDisponivel, ENT_QUOTES, 'UTF-8') ?>" <?= $regime === $regimeDisponivel ? 'selected' : '' ?>>
                                <?= htmlspecialchars($regimeDisponivel, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="vaga-filter-field">
                    <label for="filtro-ordem">Ordenar por</label>
                    <select id="filtro-ordem" name="ordem">
                        <option value="recente" <?= $ordem === 'recente' ? 'selected' : '' ?>>Mais recente</option>
                        <option value="salario" <?= $ordem === 'salario' ? 'selected' : '' ?>>Maior salário</option>
                    </select>
                </div>

                <div class="vaga-filter-actions">
                    <a href="/vagas/index.php" class="vaga-filter-clear" data-preservar-posicao>Limpar</a>
                    <button type="submit" class="vaga-filter-submit">Aplicar filtros</button>
                </div>
            </form>
        </aside>

        <section class="vacancies-content" aria-label="Resultados das vagas">
            <div class="jobs-grid">
                <?php if (!$vagas): ?>
                    <div class="job-card job-card-empty">
                        <h3 class="job-title">Nenhuma vaga encontrada</h3>
                        <div class="job-meta"><?= $empresasEncontradas ? 'Confira as empresas encontradas abaixo.' : 'Tente alterar os filtros ou buscar outro termo.' ?></div>
                        <div class="job-footer"><a href="/vagas/index.php" class="btn-apply vaga-filter-clear" data-preservar-posicao>Limpar filtros</a></div>
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
            <?php if ($q !== ''): ?>
                <section class="company-results" aria-labelledby="company-results-title">
                    <h2 id="company-results-title">Empresas cadastradas</h2>
                    <?php if (!$empresasEncontradas): ?>
                        <p class="company-results-empty">Nenhuma empresa encontrada para “<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>”.</p>
                    <?php else: ?>
                        <div class="company-results-grid">
                            <?php foreach ($empresasEncontradas as $empresaEncontrada): ?>
                                <a class="company-result-card" href="/empresa/perfil.php?id=<?= (int) $empresaEncontrada['id'] ?>">
                                    <span class="company-result-initial" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr(trim($empresaEncontrada['nome']), 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="company-result-details">
                                        <strong><?= htmlspecialchars($empresaEncontrada['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span><?= htmlspecialchars($empresaEncontrada['setor'] ?: 'Empresa cadastrada', ENT_QUOTES, 'UTF-8') ?><?= $empresaEncontrada['cidade_sede'] ? ' · ' . htmlspecialchars($empresaEncontrada['cidade_sede'], ENT_QUOTES, 'UTF-8') : '' ?></span>
                                        <small><?= (int) $empresaEncontrada['vagas_ativas'] ?> vaga<?= (int) $empresaEncontrada['vagas_ativas'] === 1 ? '' : 's' ?> aberta<?= (int) $empresaEncontrada['vagas_ativas'] === 1 ? '' : 's' ?></small>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
            <?php if ($totalPaginas > 1): ?>
                <nav class="jobs-pagination" aria-label="Paginação das vagas">
                    <a class="pagination-link <?= $paginaAtual === 1 ? 'is-disabled' : '' ?>"
                        href="<?= htmlspecialchars($urlComFiltros(['pagina' => 1]), ENT_QUOTES, 'UTF-8') ?>"
                        <?= $paginaAtual === 1 ? 'aria-disabled="true"' : '' ?>>Anterior</a>
                    <?php for ($pagina = 1; $pagina <= $totalPaginas; $pagina++): ?>
                        <a class="pagination-link <?= $paginaAtual === $pagina ? 'is-current' : '' ?>"
                            href="<?= htmlspecialchars($urlComFiltros(['pagina' => $pagina]), ENT_QUOTES, 'UTF-8') ?>"
                            <?= $paginaAtual === $pagina ? 'aria-current="page"' : '' ?>><?= $pagina ?></a>
                    <?php endfor; ?>
                    <a class="pagination-link <?= $paginaAtual === $totalPaginas ? 'is-disabled' : '' ?>"
                        href="<?= htmlspecialchars($urlComFiltros(['pagina' => $totalPaginas]), ENT_QUOTES, 'UTF-8') ?>"
                        <?= $paginaAtual === $totalPaginas ? 'aria-disabled="true"' : '' ?>>Próxima</a>
                </nav>
            <?php endif; ?>
        </section>
    </div>

    <footer class="footer">
        <div style="display: flex; align-items: baseline; gap: 6px;">
            <span style="font-family: 'Fraunces', serif; font-size: 18px; font-weight: 600; color: var(--primary);">Infodin</span>
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

    <script src="/JS/busca-vagas-sugestoes.js"></script>
    <script src="/JS/vagas-filtros.js"></script>
    <script src="/JS/tema.js"></script>

</body>

</html>