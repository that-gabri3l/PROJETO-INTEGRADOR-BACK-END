<?php
require_once __DIR__ . '/../src/sessao.php';
exigirTipo('empresa');
require_once __DIR__ . '/../src/conexao.php';

$empresaId = $_SESSION['perfil_id'] ?? null;
$candidaturaId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$empresaId || !$candidaturaId || $candidaturaId < 1) {
    header('Location: /empresa/painel.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT c.id AS candidatura_id, c.status AS candidatura_status, c.criado_em AS candidatura_criada_em,
            c.portifolio, c.mensagem, ca.nome, ca.email, ca.telefone, ca.cidade, ca.foto_perfil,
            ca.cargo, ca.area, ca.experiencia, ca.escolaridade, ca.regime_preferido, ca.linkedin, ca.sobre,
            v.titulo AS vaga_titulo
     FROM candidaturas c
     INNER JOIN candidatos ca ON ca.id = c.candidato_id
     INNER JOIN vagas v ON v.id = c.vaga_id
     WHERE c.id = :candidatura_id AND v.empresa_id = :empresa_id
     LIMIT 1'
);
$stmt->execute(['candidatura_id' => $candidaturaId, 'empresa_id' => $empresaId]);
$candidatura = $stmt->fetch(PDO::FETCH_ASSOC);

$statusCandidaturas = [
    'recebida' => 'Recebida',
    'em_analise' => 'Em análise',
    'aprovada' => 'Aprovada',
    'recusada' => 'Recusada',
];
$urlPortfolio = $candidatura['portifolio'] ?? '';
$portfolioValido = filter_var($urlPortfolio, FILTER_VALIDATE_URL)
    && in_array(strtolower((string) parse_url($urlPortfolio, PHP_URL_SCHEME)), ['http', 'https'], true);
$urlLinkedin = $candidatura['linkedin'] ?? '';
$linkedinValido = filter_var($urlLinkedin, FILTER_VALIDATE_URL)
    && in_array(strtolower((string) parse_url($urlLinkedin, PHP_URL_SCHEME)), ['http', 'https'], true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do candidato - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/CSS/perfil-candidato-empresa.css">
    <link rel="stylesheet" href="/CSS/mobile.css">
</head>
<body>
    <nav class="navbar">
        <a href="/empresa/painel.php" class="navbar-logo">
            <img src="/images/logo-claro-infodin.png" alt="Infodin" class="logo-light" style="height:40px;width:auto;object-fit:contain;">
            <img src="/images/logo-infodin.png" alt="Infodin" class="logo-dark" style="height:40px;width:auto;object-fit:contain;">
        </a>
        <button id="theme-toggle" class="theme-toggle" type="button" title="Mudar tema" aria-label="Alternar tema"><svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/></svg></button>
    </nav>

    <main class="container">
        <a href="/empresa/painel.php" class="back-link">← Voltar ao painel</a>
        <?php if (!$candidatura): ?>
            <section class="profile-card empty-state" role="status">
                <h1>Candidatura não encontrada</h1>
                <p>Este perfil não está disponível ou a candidatura não pertence a uma vaga da sua empresa.</p>
            </section>
        <?php else: ?>
            <header class="profile-header">
                <?php if (!empty($candidatura['foto_perfil'])): ?>
                    <img class="candidate-photo" src="<?= htmlspecialchars($candidatura['foto_perfil'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($candidatura['nome'], ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <div class="candidate-photo candidate-initial" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($candidatura['nome'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <div class="profile-heading">
                    <span class="eyebrow">Perfil do candidato</span>
                    <h1><?= htmlspecialchars($candidatura['nome'], ENT_QUOTES, 'UTF-8') ?></h1>
                    <p><?= htmlspecialchars($candidatura['cargo'] ?: 'Cargo não informado', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </header>

            <section class="application-summary profile-card">
                <div>
                    <span class="field-label">Candidatou-se à vaga</span>
                    <strong><?= htmlspecialchars($candidatura['vaga_titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div>
                    <span class="field-label">Data da candidatura</span>
                    <strong><?= date('d/m/Y', strtotime($candidatura['candidatura_criada_em'])) ?></strong>
                </div>
                <div>
                    <span class="field-label">Status</span>
                    <strong class="status status-<?= htmlspecialchars($candidatura['candidatura_status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($statusCandidaturas[$candidatura['candidatura_status']] ?? 'Indefinido', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </section>

            <section class="profile-card">
                <h2>Contato</h2>
                <dl class="profile-details">
                    <div><dt>E-mail</dt><dd><a href="mailto:<?= htmlspecialchars($candidatura['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($candidatura['email'], ENT_QUOTES, 'UTF-8') ?></a></dd></div>
                    <div><dt>Telefone</dt><dd><?= htmlspecialchars($candidatura['telefone'] ?: 'Não informado', ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Cidade</dt><dd><?= htmlspecialchars($candidatura['cidade'] ?: 'Não informada', ENT_QUOTES, 'UTF-8') ?></dd></div>
                </dl>
            </section>

            <section class="profile-card">
                <h2>Perfil profissional</h2>
                <dl class="profile-details">
                    <div><dt>Área de atuação</dt><dd><?= htmlspecialchars($candidatura['area'] ?: 'Não informada', ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Experiência</dt><dd><?= htmlspecialchars($candidatura['experiencia'] ?: 'Não informada', ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Escolaridade</dt><dd><?= htmlspecialchars($candidatura['escolaridade'] ?: 'Não informada', ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Regimes preferidos</dt><dd><?= htmlspecialchars($candidatura['regime_preferido'] ?: 'Não informado', ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>LinkedIn</dt><dd>
                        <?php if ($linkedinValido): ?>
                            <a href="<?= htmlspecialchars($urlLinkedin, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Abrir perfil do LinkedIn</a>
                        <?php else: ?>
                            Não informado
                        <?php endif; ?>
                    </dd></div>
                </dl>
                <?php if (!empty($candidatura['sobre'])): ?>
                    <div class="profile-text">
                        <h3>Sobre</h3>
                        <p><?= nl2br(htmlspecialchars($candidatura['sobre'], ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>
                <?php endif; ?>
            </section>

            <section class="profile-card">
                <h2>Dados enviados na candidatura</h2>
                <p class="portfolio-link">
                    <?php if ($portfolioValido): ?>
                        <a href="<?= htmlspecialchars($urlPortfolio, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Abrir currículo ou portfólio</a>
                    <?php else: ?>
                        Nenhum currículo ou portfólio informado.
                    <?php endif; ?>
                </p>
                <?php if (!empty($candidatura['mensagem'])): ?>
                    <div class="profile-text">
                        <h3>Mensagem</h3>
                        <p><?= nl2br(htmlspecialchars($candidatura['mensagem'], ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
    <script src="/JS/tema.js"></script>
</body>
</html>
