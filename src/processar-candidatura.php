<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/sessao.php';

exigirTipo('candidato');
exigirTokenCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /vagas/index.php');
    exit;
}

$vagaId = filter_input(INPUT_POST, 'vaga_id', FILTER_VALIDATE_INT);
$portfolio = trim($_POST['portfolio'] ?? '');

if (!$vagaId) {
    header('Location: /vagas/index.php?erro=' . urlencode('Vaga inválida.'));
    exit;
}

if ($portfolio === '' || !filter_var($portfolio, FILTER_VALIDATE_URL)) {
    header('Location: /vagas/detalhes.php?id=' . $vagaId . '&erro=' . urlencode('Informe uma URL válida para o currículo ou portfólio.'));
    exit;
}

$candidatoId = $_SESSION['perfil_id'] ?? null;
if (!$candidatoId) {
    header('Location: /cadastro/login.php?erro=' . urlencode('Complete seu perfil de candidato antes de se candidatar.'));
    exit;
}

try {
    $vagaStmt = $pdo->prepare('SELECT id FROM vagas WHERE id = :id AND status = "ativa" LIMIT 1');
    $vagaStmt->execute(['id' => $vagaId]);
    if (!$vagaStmt->fetchColumn()) {
        header('Location: /vagas/index.php?erro=' . urlencode('Esta vaga não está disponível.'));
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO candidaturas (candidato_id, vaga_id, portifolio, status)
        VALUES (:candidato_id, :vaga_id, :portifolio, "recebida")'
    );
    $stmt->execute([
        'candidato_id' => $candidatoId,
        'vaga_id' => $vagaId,
        'portifolio' => $portfolio,
    ]);

    header('Location: /candidato/painel.php?candidatura=sucesso');
    exit;
} catch (PDOException $e) {
    $mensagem = (int) $e->getCode() === 23000
        ? 'Você já se candidatou a esta vaga.'
        : 'Não foi possível enviar sua candidatura.';

    header('Location: /vagas/detalhes.php?id=' . $vagaId . '&erro=' . urlencode($mensagem));
    exit;
}
