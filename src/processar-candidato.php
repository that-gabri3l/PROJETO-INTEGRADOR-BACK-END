<?php
require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /candidato/cadastro.php');
    exit;
}

function voltarComErro(string $mensagem): void
{
    header('Location: /candidato/cadastro.php?erro=' . urlencode($mensagem));
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$telefone = trim($_POST['telefone'] ?? '');
$cidade = trim($_POST['cidade'] ?? '');
$dataNascimento = trim($_POST['data_nascimento'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';
$cargo = trim($_POST['cargo'] ?? '');
$area = trim($_POST['area'] ?? '');
$experiencia = trim($_POST['experiencia'] ?? '');
$escolaridade = trim($_POST['escolaridade'] ?? '');
$regimePreferido = trim($_POST['regime_preferido'] ?? '');
$linkedin = trim($_POST['linkedin'] ?? '');
$sobre = trim($_POST['sobre'] ?? '');
$notificacoes = isset($_POST['notificacoes']) ? 1 : 0;

if ($nome === '' || $email === '' || $cidade === '' || $senha === '' || $confirmarSenha === '' || $cargo === '' || $area === '' || $experiencia === '') {
    voltarComErro('Preencha todos os campos obrigatórios.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    voltarComErro('Informe um e-mail válido.');
}

if (strlen($senha) < 8) {
    voltarComErro('A senha deve ter pelo menos 8 caracteres.');
}

if (!hash_equals($senha, $confirmarSenha)) {
    voltarComErro('As senhas não conferem.');
}

if ($linkedin !== '' && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
    voltarComErro('Informe uma URL válida para o LinkedIn.');
}

$caminhoFoto = null;
if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
    $arquivo = $_FILES['foto_perfil'];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        voltarComErro('Não foi possível enviar a foto de perfil.');
    }

    if ($arquivo['size'] > 2 * 1024 * 1024) {
        voltarComErro('A foto deve ter no máximo 2 MB.');
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipoReal = $finfo->file($_FILES['foto_perfil']['tmp_name']);

    if (!isset($tiposPermitidos[$tipoReal])) {
        voltarComErro('Envie uma imagem JPG ou PNG.');
    }

    $pastaDestino = __DIR__ . '/uploads/fotos_perfil/';
    if (!is_dir($pastaDestino) && !mkdir($pastaDestino, 0755, true)) {
        voltarComErro('Não foi possível preparar o armazenamento da foto.');
    }

    $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$tipoReal];
    $caminhoCompleto = $pastaDestino . $nomeArquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
        voltarComErro('Não foi possível salvar a foto de perfil.');
    }

    $caminhoFoto = '/src/uploads/fotos_perfil/' . $nomeArquivo;
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    $pdo->beginTransaction();

    $stmtUsuario = $pdo->prepare('INSERT INTO usuarios (tipo, email, senha) VALUES (\'candidato\', :email, :senha)');
    $stmtUsuario->execute(['email' => $email, 'senha' => $senhaHash]);
    $usuarioId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'INSERT INTO candidatos
            (usuario_id, nome, email, senha, telefone, cidade, data_nascimento, foto_perfil,
             cargo, area, experiencia, escolaridade, regime_preferido, linkedin, sobre, notificacoes)
         VALUES
            (:usuario_id, :nome, :email, :senha, :telefone, :cidade, :data_nascimento, :foto_perfil,
             :cargo, :area, :experiencia, :escolaridade, :regime_preferido, :linkedin, :sobre, :notificacoes)' 
    );

    $stmt->execute([
        'usuario_id' => $usuarioId,
        'nome' => $nome,
        'email' => $email,
        'senha' => $senhaHash,
        'telefone' => $telefone !== '' ? $telefone : null,
        'cidade' => $cidade,
        'data_nascimento' => $dataNascimento !== '' ? $dataNascimento : null,
        'foto_perfil' => $caminhoFoto,
        'cargo' => $cargo,
        'area' => $area,
        'experiencia' => $experiencia,
        'escolaridade' => $escolaridade !== '' ? $escolaridade : null,
        'regime_preferido' => $regimePreferido !== '' ? $regimePreferido : null,
        'linkedin' => $linkedin !== '' ? $linkedin : null,
        'sobre' => $sobre !== '' ? $sobre : null,
        'notificacoes' => $notificacoes,
    ]);

    $pdo->commit();
    header('Location: /cadastro/login.php?cadastro=sucesso');
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ((int) $e->getCode() === 23000) {
        voltarComErro('Este e-mail já está cadastrado no sistema.');
    }

    voltarComErro('Não foi possível concluir o cadastro. Tente novamente.');
}
?>