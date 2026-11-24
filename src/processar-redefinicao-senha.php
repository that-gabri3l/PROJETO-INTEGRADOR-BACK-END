<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/sessao.php';
require_once __DIR__ . '/recuperacao-senha.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cadastro/recuperar-senha.php');
    exit;
}

exigirTokenCsrf();

$token = $_POST['token'] ?? '';
$senha = $_POST['senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';
$token = is_string($token) ? $token : '';
$senha = is_string($senha) ? $senha : '';
$confirmarSenha = is_string($confirmarSenha) ? $confirmarSenha : '';

if (!tokenRecuperacaoTemFormatoValido($token)) {
    header('Location: ../cadastro/recuperar-senha.php?erro=indisponivel');
    exit;
}

$urlRetorno = '../cadastro/redefinir-senha.php?token=' . rawurlencode($token);

if (strlen($senha) < 8 || strlen($senha) > 72) {
    header('Location: ' . $urlRetorno . '&erro=senha');
    exit;
}

if (!hash_equals($senha, $confirmarSenha)) {
    header('Location: ' . $urlRetorno . '&erro=confirmacao');
    exit;
}

try {
    $pdo->beginTransaction();

    $recuperacao = buscarRecuperacaoAtiva($pdo, hash('sha256', $token), true);
    if (!$recuperacao) {
        $pdo->rollBack();
        header('Location: ../cadastro/recuperar-senha.php?erro=indisponivel');
        exit;
    }

    if ($recuperacao['usuario_id'] !== null) {
        $contaStmt = $pdo->prepare(
            'SELECT senha FROM usuarios WHERE id = :id AND ativo = 1 FOR UPDATE'
        );
        $contaStmt->execute(['id' => (int) $recuperacao['usuario_id']]);
        $conta = $contaStmt->fetch(PDO::FETCH_ASSOC);

        if (!$conta) {
            $pdo->rollBack();
            header('Location: ../cadastro/recuperar-senha.php?erro=indisponivel');
            exit;
        }

        if (password_verify($senha, $conta['senha'])) {
            $pdo->rollBack();
            header('Location: ' . $urlRetorno . '&erro=igual');
            exit;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $atualizarStmt = $pdo->prepare(
            'UPDATE usuarios SET senha = :senha WHERE id = :id AND ativo = 1'
        );
        $atualizarStmt->execute([
            'senha' => $senhaHash,
            'id' => (int) $recuperacao['usuario_id'],
        ]);
    } else {
        $contaStmt = $pdo->prepare(
            'SELECT senha FROM candidatos WHERE id = :id AND usuario_id IS NULL FOR UPDATE'
        );
        $contaStmt->execute(['id' => (int) $recuperacao['candidato_id']]);
        $conta = $contaStmt->fetch(PDO::FETCH_ASSOC);

        if (!$conta) {
            $pdo->rollBack();
            header('Location: ../cadastro/recuperar-senha.php?erro=indisponivel');
            exit;
        }

        if (password_verify($senha, $conta['senha'])) {
            $pdo->rollBack();
            header('Location: ' . $urlRetorno . '&erro=igual');
            exit;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $atualizarStmt = $pdo->prepare(
            'UPDATE candidatos SET senha = :senha WHERE id = :id AND usuario_id IS NULL'
        );
        $atualizarStmt->execute([
            'senha' => $senhaHash,
            'id' => (int) $recuperacao['candidato_id'],
        ]);
    }

    if ($atualizarStmt->rowCount() !== 1) {
        $pdo->rollBack();
        header('Location: ../cadastro/recuperar-senha.php?erro=indisponivel');
        exit;
    }

    $invalidarStmt = $pdo->prepare(
        'UPDATE recuperacoes_senha
         SET usado_em = NOW()
         WHERE usado_em IS NULL
           AND (usuario_id = :usuario_id OR candidato_id = :candidato_id)'
    );
    $invalidarStmt->execute([
        'usuario_id' => $recuperacao['usuario_id'],
        'candidato_id' => $recuperacao['candidato_id'],
    ]);

    $pdo->commit();
    header('Location: ../cadastro/login.php?senha=alterada');
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Redefinição de senha falhou no banco de dados: ' . $e->getMessage());
    header('Location: ' . $urlRetorno . '&erro=indisponivel');
    exit;
}
