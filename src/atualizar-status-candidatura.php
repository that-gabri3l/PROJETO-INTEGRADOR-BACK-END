<?php
require_once __DIR__ . '/sessao.php';
exigirTipo('empresa');
exigirTokenCsrf();
require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /empresa/painel.php');
    exit;
}

$candidaturaId = filter_var($_POST['candidatura_id'] ?? null, FILTER_VALIDATE_INT);
$empresaId = $_SESSION['perfil_id'] ?? null;

function redirecionarStatusCandidatura(?int $candidaturaId, string $parametro, string $resultado = '1'): void
{
    $destino = $candidaturaId && $candidaturaId > 0
        ? '/empresa/ver-candidato.php?' . http_build_query(['id' => $candidaturaId, $parametro => $resultado])
        : '/empresa/painel.php';
    header('Location: ' . $destino);
    exit;
}

if (!$candidaturaId || $candidaturaId < 1 || !$empresaId) {
    redirecionarStatusCandidatura(null, 'erro');
}

$novoStatus = $_POST['status'] ?? '';
$statusPermitidos = ['em_analise', 'aprovada', 'recusada'];
if (!is_string($novoStatus) || !in_array($novoStatus, $statusPermitidos, true)) {
    redirecionarStatusCandidatura($candidaturaId, 'erro', 'invalida');
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare(
        'SELECT c.status
        FROM candidaturas c
        INNER JOIN vagas v ON v.id = c.vaga_id
        WHERE c.id = :candidatura_id AND v.empresa_id = :empresa_id
        LIMIT 1
        FOR UPDATE'
    );
    $stmt->execute(['candidatura_id' => $candidaturaId, 'empresa_id' => $empresaId]);
    $statusAtual = $stmt->fetchColumn();

    if ($statusAtual === false) {
        $pdo->rollBack();
        redirecionarStatusCandidatura(null, 'erro');
    }

    $transicoesPermitidas = [
        'recebida' => ['em_analise', 'recusada'],
        'em_analise' => ['aprovada', 'recusada'],
        'aprovada' => [],
        'recusada' => [],
    ];
    if (!in_array($novoStatus, $transicoesPermitidas[$statusAtual] ?? [], true)) {
        $pdo->rollBack();
        redirecionarStatusCandidatura($candidaturaId, 'erro', 'transicao');
    }

    $stmt = $pdo->prepare('UPDATE candidaturas SET status = :status WHERE id = :id');
    $stmt->execute(['status' => $novoStatus, 'id' => $candidaturaId]);
    $pdo->commit();

    redirecionarStatusCandidatura($candidaturaId, 'atualizado');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Atualização de status da candidatura falhou: ' . $e->getMessage());
    redirecionarStatusCandidatura($candidaturaId, 'erro', 'falha');
}
