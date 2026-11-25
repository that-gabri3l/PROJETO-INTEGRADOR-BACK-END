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
$acao = $_POST['acao'] ?? 'salvar';
$candidatoId = $_SESSION['perfil_id'] ?? null;

if (!$vagaId || !$candidatoId) {
    header('Location: /vagas/index.php');
    exit;
}

try {
    $stmtVaga = $pdo->prepare('SELECT id FROM vagas WHERE id = :vaga_id AND status = "ativa" LIMIT 1');
    $stmtVaga->execute(['vaga_id' => $vagaId]);
    if (!$stmtVaga->fetchColumn()) {
        header('Location: /vagas/index.php');
        exit;
    }

    if ($acao === 'remover') {
        $stmt = $pdo->prepare('DELETE FROM vagas_salvas WHERE candidato_id = :candidato_id AND vaga_id = :vaga_id');
        $stmt->execute(['candidato_id' => $candidatoId, 'vaga_id' => $vagaId]);
        $acaoResultado = 'removida';
    } else {
        $stmt = $pdo->prepare('INSERT IGNORE INTO vagas_salvas (candidato_id, vaga_id) VALUES (:candidato_id, :vaga_id)');
        $stmt->execute(['candidato_id' => $candidatoId, 'vaga_id' => $vagaId]);
        $acaoResultado = 'salva';
    }
} catch (PDOException $e) {
    $acaoResultado = 'erro';
}

$origem = $_POST['origem'] ?? '/vagas/index.php';
$origem = str_starts_with($origem, '/') ? $origem : '/vagas/index.php';
$separador = str_contains($origem, '?') ? '&' : '?';
header('Location: ' . $origem . $separador . 'vaga=' . $acaoResultado);
exit;
