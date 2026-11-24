<?php
require_once __DIR__ . '/sessao.php';
exigirTipo('empresa');
exigirTokenCsrf();
require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /empresa/editar-perfil.php');
    exit;
}

function redirecionarComErroEmpresa(string $mensagem): void
{
    header('Location: /empresa/editar-perfil.php?erro=' . urlencode($mensagem));
    exit;
}

$empresaId = $_SESSION['perfil_id'] ?? null;
if (!$empresaId) {
    header('Location: /cadastro/login.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$razaoSocial = trim($_POST['razao_social'] ?? '');
$cnpjDigitos = preg_replace('/\D/', '', (string) ($_POST['cnpj'] ?? ''));
$setor = trim($_POST['setor'] ?? '');
$porte = trim($_POST['porte'] ?? '');
$email = strtolower(trim($_POST['email_corporativo'] ?? ''));
$telefone = trim($_POST['telefone'] ?? '');
$cidade = trim($_POST['cidade_sede'] ?? '');
$site = trim($_POST['site'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$senhaAtual = $_POST['senha_atual'] ?? '';
$novaSenha = $_POST['nova_senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';

if (!is_string($senhaAtual) || !is_string($novaSenha) || !is_string($confirmarSenha)) {
    redirecionarComErroEmpresa('Os dados de senha informados são inválidos.');
}

$solicitouAlteracaoSenha = $senhaAtual !== '' || $novaSenha !== '' || $confirmarSenha !== '';
if ($solicitouAlteracaoSenha) {
    if ($senhaAtual === '' || $novaSenha === '' || $confirmarSenha === '') {
        redirecionarComErroEmpresa('Preencha a senha atual, a nova senha e a confirmação para alterar a senha.');
    }

    if (strlen($novaSenha) < 8) {
        redirecionarComErroEmpresa('A nova senha deve ter pelo menos 8 caracteres.');
    }

    if (!hash_equals($novaSenha, $confirmarSenha)) {
        redirecionarComErroEmpresa('A nova senha e a confirmação não correspondem.');
    }
}

if ($nome === '' || $cnpjDigitos === '' || $setor === '' || $porte === '' || $email === '' || $cidade === '') {
    redirecionarComErroEmpresa('Preencha nome, CNPJ, setor, porte, e-mail corporativo e cidade.');
}

if (strlen($nome) > 150 || strlen($razaoSocial) > 180 || strlen($cnpjDigitos) !== 14) {
    redirecionarComErroEmpresa('Confira o nome, a razão social e o CNPJ informados.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirecionarComErroEmpresa('Informe um e-mail corporativo válido.');
}

if (!in_array($setor, ['Tecnologia', 'Finanças', 'Saúde', 'Educação', 'Varejo', 'Logística', 'Outro'], true)) {
    redirecionarComErroEmpresa('Selecione um setor válido.');
}

if (!in_array($porte, ['Microempresa', 'Pequena', 'Média', 'Grande'], true)) {
    redirecionarComErroEmpresa('Selecione um porte válido.');
}

if ($site !== '' && !filter_var($site, FILTER_VALIDATE_URL)) {
    redirecionarComErroEmpresa('Informe uma URL válida para o site.');
}

$cnpj = substr($cnpjDigitos, 0, 2) . '.' . substr($cnpjDigitos, 2, 3) . '.' . substr($cnpjDigitos, 5, 3) . '/' . substr($cnpjDigitos, 8, 4) . '-' . substr($cnpjDigitos, 12, 2);

$stmtAtual = $pdo->prepare('SELECT usuario_id, logo FROM empresas WHERE id = :id LIMIT 1');
$stmtAtual->execute(['id' => $empresaId]);
$empresaAtual = $stmtAtual->fetch();
if (!$empresaAtual) {
    redirecionarComErroEmpresa('Não foi possível localizar o perfil da empresa.');
}

$senhaHash = null;
if ($solicitouAlteracaoSenha) {
    $sessaoUsuarioId = filter_var($_SESSION['usuario_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$empresaAtual['usuario_id']
        || !$sessaoUsuarioId
        || (int) $sessaoUsuarioId !== (int) $empresaAtual['usuario_id']) {
        redirecionarComErroEmpresa('Não foi possível validar sua conta. Faça login novamente.');
    }

    $stmtUsuario = $pdo->prepare('SELECT tipo, senha, ativo FROM usuarios WHERE id = :id LIMIT 1');
    $stmtUsuario->execute(['id' => $empresaAtual['usuario_id']]);
    $usuarioAtual = $stmtUsuario->fetch();

    if (!$usuarioAtual || $usuarioAtual['tipo'] !== 'empresa' || !(bool) $usuarioAtual['ativo']) {
        redirecionarComErroEmpresa('Não foi possível validar sua conta. Faça login novamente.');
    }

    if (!password_verify($senhaAtual, $usuarioAtual['senha'])) {
        redirecionarComErroEmpresa('A senha atual não confere.');
    }

    if (password_verify($novaSenha, $usuarioAtual['senha'])) {
        redirecionarComErroEmpresa('A nova senha deve ser diferente da senha atual.');
    }

    $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
}

$caminhoLogo = $empresaAtual['logo'];
$novoArquivo = null;
if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $arquivo = $_FILES['logo'];
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $mensagemUpload = match ($arquivo['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'A logo excede o limite permitido. Envie uma imagem de até 2 MB.',
            UPLOAD_ERR_PARTIAL => 'A logo foi enviada parcialmente. Tente novamente.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'O servidor não conseguiu armazenar temporariamente a logo.',
            default => 'Não foi possível enviar a logo.',
        };
        redirecionarComErroEmpresa($mensagemUpload);
    }
    if ($arquivo['size'] > 2 * 1024 * 1024) {
        redirecionarComErroEmpresa('A logo deve ter no máximo 2 MB.');
    }
    if (!class_exists('finfo')) {
        error_log('Atualização de empresa: extensão PHP fileinfo indisponível.');
        redirecionarComErroEmpresa('O servidor não está configurado para validar imagens.');
    }

    $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipoReal = $finfo->file($arquivo['tmp_name']);
    if (!isset($tiposPermitidos[$tipoReal])) {
        redirecionarComErroEmpresa('O arquivo selecionado não é uma imagem JPG ou PNG válida.');
    }

    $pastaDestino = __DIR__ . '/uploads/logos_empresas/';
    if (!is_dir($pastaDestino) && !mkdir($pastaDestino, 0755, true)) {
        error_log('Atualização de empresa: falha ao criar diretório de logos: ' . $pastaDestino);
        redirecionarComErroEmpresa('Não foi possível preparar o armazenamento da logo.');
    }

    $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$tipoReal];
    $novoArquivo = $pastaDestino . $nomeArquivo;
    if (!move_uploaded_file($arquivo['tmp_name'], $novoArquivo)) {
        error_log('Atualização de empresa: move_uploaded_file falhou para ' . $novoArquivo);
        redirecionarComErroEmpresa('Não foi possível salvar a logo.');
    }
    $caminhoLogo = '/src/uploads/logos_empresas/' . $nomeArquivo;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'UPDATE empresas
         SET nome = :nome, razao_social = :razao_social, cnpj = :cnpj, setor = :setor,
             porte = :porte, email_corporativo = :email, telefone = :telefone,
             site = :site, cidade_sede = :cidade, descricao = :descricao, logo = :logo
         WHERE id = :id'
    );
    $stmt->execute([
        'nome' => $nome,
        'razao_social' => $razaoSocial !== '' ? $razaoSocial : null,
        'cnpj' => $cnpj,
        'setor' => $setor,
        'porte' => $porte,
        'email' => $email,
        'telefone' => $telefone !== '' ? $telefone : null,
        'site' => $site !== '' ? $site : null,
        'cidade' => $cidade,
        'descricao' => $descricao !== '' ? $descricao : null,
        'logo' => $caminhoLogo,
        'id' => $empresaId,
    ]);

    if ($empresaAtual['usuario_id']) {
        $stmtUsuario = $pdo->prepare('UPDATE usuarios SET email = :email WHERE id = :id');
        $stmtUsuario->execute(['email' => $email, 'id' => $empresaAtual['usuario_id']]);
    }

    if ($senhaHash !== null) {
        $stmtSenha = $pdo->prepare(
            'UPDATE usuarios SET senha = :senha WHERE id = :id AND tipo = \'empresa\' AND ativo = 1'
        );
        $stmtSenha->execute([
            'senha' => $senhaHash,
            'id' => $empresaAtual['usuario_id'],
        ]);

        if ($stmtSenha->rowCount() !== 1) {
            $pdo->rollBack();
            if ($novoArquivo && is_file($novoArquivo)) {
                unlink($novoArquivo);
            }
            redirecionarComErroEmpresa('Não foi possível validar sua conta. Faça login novamente.');
        }
    }

    $pdo->commit();
    $_SESSION['usuario_nome'] = $nome;
    $_SESSION['usuario_email'] = $email;
    if ($senhaHash !== null) {
        session_regenerate_id(true);
    }

    if ($novoArquivo && !empty($empresaAtual['logo']) && str_starts_with($empresaAtual['logo'], '/src/uploads/logos_empresas/')) {
        $logoAnterior = __DIR__ . '/uploads/logos_empresas/' . basename($empresaAtual['logo']);
        if (is_file($logoAnterior)) {
            unlink($logoAnterior);
        }
    }

    $destino = '/empresa/editar-perfil.php?salvo=1';
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
        redirecionarComErroEmpresa('Este e-mail corporativo ou CNPJ já está em uso.');
    }

    error_log('Atualização de empresa falhou: ' . $e->getMessage());
    redirecionarComErroEmpresa('Não foi possível atualizar o perfil. Tente novamente.');
}