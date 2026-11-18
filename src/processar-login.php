<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/sessao.php';

exigirTokenCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /cadastro/login.php');
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    header('Location: /cadastro/login.php?erro=' . urlencode('Informe e-mail e senha.'));
    exit;
}

try {
    $usuario = null;

    $stmt = $pdo->prepare('SELECT id, tipo, email, senha, ativo FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        if (!(bool) $usuario['ativo']) {
            header('Location: /cadastro/login.php?erro=' . urlencode('Esta conta está desativada.'));
            exit;
        }

        if (!password_verify($senha, $usuario['senha'])) {
            header('Location: /cadastro/login.php?erro=' . urlencode('E-mail ou senha inválidos.'));
            exit;
        }

        $perfil = null;
        if ($usuario['tipo'] === 'candidato') {
            $perfilStmt = $pdo->prepare('SELECT id, nome, cidade, foto_perfil FROM candidatos WHERE usuario_id = :usuario_id LIMIT 1');
            $perfilStmt->execute(['usuario_id' => $usuario['id']]);
            $perfil = $perfilStmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $perfilStmt = $pdo->prepare('SELECT id, nome, logo FROM empresas WHERE usuario_id = :usuario_id LIMIT 1');
            $perfilStmt->execute(['usuario_id' => $usuario['id']]);
            $perfil = $perfilStmt->fetch(PDO::FETCH_ASSOC);
        }

        $_SESSION['usuario_id'] = (int) $usuario['id'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['perfil_id'] = $perfil ? (int) $perfil['id'] : null;
        $_SESSION['usuario_nome'] = $perfil ? $perfil['nome'] : 'Usuário';
        $_SESSION['cidade'] = $perfil && isset($perfil['cidade']) ? $perfil['cidade'] : null;

        session_regenerate_id(true);
    } else {
        $stmt = $pdo->prepare('SELECT id, nome, email, senha, cidade, foto_perfil FROM candidatos WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $candidato = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$candidato || !password_verify($senha, $candidato['senha'])) {
            header('Location: /cadastro/login.php?erro=' . urlencode('E-mail ou senha inválidos.'));
            exit;
        }

        $_SESSION['usuario_id'] = (int) $candidato['id'];
        $_SESSION['usuario_tipo'] = 'candidato';
        $_SESSION['usuario_email'] = $candidato['email'];
        $_SESSION['perfil_id'] = (int) $candidato['id'];
        $_SESSION['usuario_nome'] = $candidato['nome'];
        $_SESSION['cidade'] = $candidato['cidade'];

        session_regenerate_id(true);
    }

    $destino = $_SESSION['usuario_tipo'] === 'empresa'
        ? '/empresa/painel.php'
        : '/candidato/painel.php';

    header('Location: ' . $destino);
    exit;
} catch (PDOException $e) {
    header('Location: /cadastro/login.php?erro=' . urlencode('Não foi possível entrar agora. Tente novamente.'));
    exit;
}
