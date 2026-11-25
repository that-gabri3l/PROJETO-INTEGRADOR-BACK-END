<?php
require_once __DIR__ . '/sessao.php';
exigirTipo('candidato');
exigirTokenCsrf();
require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /candidato/editar-perfil.php');
    exit;
}

function redirecionarComErroPerfil(string $mensagem): void
{
    header('Location: /candidato/editar-perfil.php?erro=' . urlencode($mensagem));
    exit;
}

$candidatoId = $_SESSION['perfil_id'] ?? null;
if (!$candidatoId) {
    header('Location: /cadastro/login.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$telefone = trim($_POST['telefone'] ?? '');
$cidade = trim($_POST['cidade'] ?? '');
$dataNascimento = trim($_POST['data_nascimento'] ?? '');
$cargo = trim($_POST['cargo'] ?? '');
$area = trim($_POST['area'] ?? '');
$experiencia = trim($_POST['experiencia'] ?? '');
$escolaridade = trim($_POST['escolaridade'] ?? '');
$regimePreferido = trim($_POST['regime_preferido'] ?? '');
$linkedin = trim($_POST['linkedin'] ?? '');
$sobre = trim($_POST['sobre'] ?? '');
$notificacoes = isset($_POST['notificacoes']) ? 1 : 0;
$senhaAtual = $_POST['senha_atual'] ?? '';
$novaSenha = $_POST['nova_senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';

if (!is_string($senhaAtual) || !is_string($novaSenha) || !is_string($confirmarSenha)) {
    redirecionarComErroPerfil('Os dados de senha informados são inválidos.');
}

$solicitouAlteracaoSenha = $senhaAtual !== '' || $novaSenha !== '' || $confirmarSenha !== '';
if ($solicitouAlteracaoSenha) {
    if ($senhaAtual === '' || $novaSenha === '' || $confirmarSenha === '') {
        redirecionarComErroPerfil('Preencha a senha atual, a nova senha e a confirmação para alterar sua senha.');
    }

    if (strlen($novaSenha) < 8) {
        redirecionarComErroPerfil('A nova senha deve ter pelo menos 8 caracteres.');
    }

    if (!hash_equals($novaSenha, $confirmarSenha)) {
        redirecionarComErroPerfil('A nova senha e a confirmação não correspondem.');
    }
}

if ($nome === '' || $email === '' || $cidade === '') {
    redirecionarComErroPerfil('Nome, e-mail e cidade são obrigatórios.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirecionarComErroPerfil('Informe um e-mail válido.');
}

if ($linkedin !== '' && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
    redirecionarComErroPerfil('Informe uma URL válida para o LinkedIn.');
}

if ($dataNascimento !== '') {
    $dataValida = DateTime::createFromFormat('!Y-m-d', $dataNascimento);
    if (!$dataValida || $dataValida->format('Y-m-d') !== $dataNascimento) {
        redirecionarComErroPerfil('Informe uma data de nascimento válida.');
    }
}

$stmtAtual = $pdo->prepare('SELECT usuario_id, foto_perfil, senha FROM candidatos WHERE id = :id LIMIT 1');
$stmtAtual->execute(['id' => $candidatoId]);
$perfilAtual = $stmtAtual->fetch();
if (!$perfilAtual) {
    redirecionarComErroPerfil('Não foi possível localizar seu perfil.');
}

$senhaHash = null;
if ($solicitouAlteracaoSenha) {
    $sessaoUsuarioId = filter_var($_SESSION['usuario_id'] ?? null, FILTER_VALIDATE_INT);

    if ($perfilAtual['usuario_id'] !== null) {
        if (!$sessaoUsuarioId || (int) $sessaoUsuarioId !== (int) $perfilAtual['usuario_id']) {
            redirecionarComErroPerfil('Não foi possível validar sua conta. Faça login novamente.');
        }

        $stmtUsuario = $pdo->prepare('SELECT tipo, senha, ativo FROM usuarios WHERE id = :id LIMIT 1');
        $stmtUsuario->execute(['id' => $perfilAtual['usuario_id']]);
        $usuarioAtual = $stmtUsuario->fetch();

        if (!$usuarioAtual || $usuarioAtual['tipo'] !== 'candidato' || !(bool) $usuarioAtual['ativo']) {
            redirecionarComErroPerfil('Não foi possível validar sua conta. Faça login novamente.');
        }

        $hashSenhaAtual = $usuarioAtual['senha'];
    } else {
        if (!$sessaoUsuarioId || (int) $sessaoUsuarioId !== (int) $candidatoId) {
            redirecionarComErroPerfil('Não foi possível validar sua conta. Faça login novamente.');
        }

        $hashSenhaAtual = $perfilAtual['senha'];
    }

    if (!password_verify($senhaAtual, $hashSenhaAtual)) {
        redirecionarComErroPerfil('A senha atual não confere.');
    }

    if (password_verify($novaSenha, $hashSenhaAtual)) {
        redirecionarComErroPerfil('A nova senha deve ser diferente da senha atual.');
    }

    $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
}

$caminhoFoto = $perfilAtual['foto_perfil'];
$novoArquivo = null;

if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
    $arquivo = $_FILES['foto_perfil'];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        redirecionarComErroPerfil('Não foi possível enviar a foto.');
    }
    if ($arquivo['size'] > 2 * 1024 * 1024) {
        redirecionarComErroPerfil('A foto deve ter no máximo 2 MB.');
    }

    $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipoReal = $finfo->file($arquivo['tmp_name']);
    if (!isset($tiposPermitidos[$tipoReal])) {
        redirecionarComErroPerfil('Envie uma imagem JPG ou PNG.');
    }

    $pastaDestino = __DIR__ . '/uploads/fotos_perfil/';
    if (!is_dir($pastaDestino) && !mkdir($pastaDestino, 0755, true)) {
        redirecionarComErroPerfil('Não foi possível preparar o armazenamento da foto.');
    }

    $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$tipoReal];
    $novoArquivo = $pastaDestino . $nomeArquivo;
    if (!move_uploaded_file($arquivo['tmp_name'], $novoArquivo)) {
        redirecionarComErroPerfil('Não foi possível salvar a foto.');
    }
    $caminhoFoto = '/src/uploads/fotos_perfil/' . $nomeArquivo;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'UPDATE candidatos
         SET nome = :nome, email = :email, telefone = :telefone, cidade = :cidade,
             data_nascimento = :data_nascimento, foto_perfil = :foto_perfil, cargo = :cargo,
             area = :area, experiencia = :experiencia, escolaridade = :escolaridade,
             regime_preferido = :regime_preferido, linkedin = :linkedin, sobre = :sobre,
             notificacoes = :notificacoes
         WHERE id = :id'
    );
    $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone !== '' ? $telefone : null,
        'cidade' => $cidade,
        'data_nascimento' => $dataNascimento !== '' ? $dataNascimento : null,
        'foto_perfil' => $caminhoFoto,
        'cargo' => $cargo !== '' ? $cargo : null,
        'area' => $area !== '' ? $area : null,
        'experiencia' => $experiencia !== '' ? $experiencia : null,
        'escolaridade' => $escolaridade !== '' ? $escolaridade : null,
        'regime_preferido' => $regimePreferido !== '' ? $regimePreferido : null,
        'linkedin' => $linkedin !== '' ? $linkedin : null,
        'sobre' => $sobre !== '' ? $sobre : null,
        'notificacoes' => $notificacoes,
        'id' => $candidatoId,
    ]);

    if ($perfilAtual['usuario_id']) {
        $stmtUsuario = $pdo->prepare('UPDATE usuarios SET email = :email WHERE id = :id');
        $stmtUsuario->execute(['email' => $email, 'id' => $perfilAtual['usuario_id']]);
    }

    if ($senhaHash !== null) {
        if ($perfilAtual['usuario_id'] !== null) {
            $stmtSenhaUsuario = $pdo->prepare(
                'UPDATE usuarios SET senha = :senha WHERE id = :id AND tipo = \'candidato\' AND ativo = 1'
            );
            $stmtSenhaUsuario->execute([
                'senha' => $senhaHash,
                'id' => $perfilAtual['usuario_id'],
            ]);

            if ($stmtSenhaUsuario->rowCount() !== 1) {
                $pdo->rollBack();
                if ($novoArquivo && is_file($novoArquivo)) {
                    unlink($novoArquivo);
                }
                redirecionarComErroPerfil('Não foi possível validar sua conta. Faça login novamente.');
            }

            $stmtSenhaPerfil = $pdo->prepare(
                'UPDATE candidatos SET senha = :senha WHERE id = :id AND usuario_id = :usuario_id'
            );
            $stmtSenhaPerfil->execute([
                'senha' => $senhaHash,
                'id' => $candidatoId,
                'usuario_id' => $perfilAtual['usuario_id'],
            ]);
        } else {
            $stmtSenhaPerfil = $pdo->prepare(
                'UPDATE candidatos SET senha = :senha WHERE id = :id AND usuario_id IS NULL'
            );
            $stmtSenhaPerfil->execute([
                'senha' => $senhaHash,
                'id' => $candidatoId,
            ]);
        }

        if ($stmtSenhaPerfil->rowCount() !== 1) {
            $pdo->rollBack();
            if ($novoArquivo && is_file($novoArquivo)) {
                unlink($novoArquivo);
            }
            redirecionarComErroPerfil('Não foi possível salvar a nova senha. Tente novamente.');
        }
    }

    $pdo->commit();

    $_SESSION['usuario_nome'] = $nome;
    $_SESSION['usuario_email'] = $email;
    $_SESSION['cidade'] = $cidade;
    if ($senhaHash !== null) {
        session_regenerate_id(true);
    }

    $destino = '/candidato/painel.php?salvo=1';
    if ($senhaHash !== null) {
        $destino .= '&senha=alterada';
    }
    header('Location: ' . $destino);
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($novoArquivo && is_file($novoArquivo)) {
        unlink($novoArquivo);
    }

    if ((int) $e->getCode() === 23000) {
        redirecionarComErroPerfil('Este e-mail já está em uso por outra conta.');
    }

    redirecionarComErroPerfil('Não foi possível atualizar o perfil. Tente novamente.');
}