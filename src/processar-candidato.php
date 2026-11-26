<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/sessao.php';
require_once __DIR__ . '/Helpers/ValidacoesFormulario.php';

use Infodin\Helpers\ValidacoesFormulario;

exigirTokenCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /candidato/cadastro.php');
    exit;
}

function voltarComErro(string $mensagem, int $etapa = 1): void
{
    $camposPermitidos = [
        'nome', 'email', 'telefone', 'cidade', 'data_nascimento',
        'cargo', 'area', 'experiencia', 'escolaridade', 'regime_preferido',
        'linkedin', 'sobre',
    ];
    $dados = [];
    foreach ($camposPermitidos as $campo) {
        $valor = $_POST[$campo] ?? '';
        $dados[$campo] = is_string($valor) ? substr($valor, 0, 5000) : '';
    }
    $dados['notificacoes'] = isset($_POST['notificacoes']) ? 'sim' : '';
    $_SESSION['cadastro_candidato_dados'] = $dados;

    header('Location: /candidato/cadastro.php?erro=' . urlencode($mensagem) . '&etapa=' . max(1, min(3, $etapa)));
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

if ($nome === '' || $email === '' || $cidade === '') {
    voltarComErro('Nome, e-mail e cidade são obrigatórios.', 1);
}

if ($cargo === '' || $area === '' || $experiencia === '') {
    voltarComErro('Preencha cargo, área e nível de experiência.', 2);
}

if ($senha === '' || $confirmarSenha === '') {
    voltarComErro('Informe e confirme sua senha.', 3);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    voltarComErro('Informe um e-mail válido.', 1);
}

if (!ValidacoesFormulario::telefoneBrasileiro($telefone)) {
    voltarComErro('Informe um telefone com DDD e 10 ou 11 dígitos.', 1);
}

if (!ValidacoesFormulario::dataNascimentoValida($dataNascimento)) {
    voltarComErro('Informe uma data de nascimento válida que não seja futura.', 1);
}

if (strlen($senha) < 8) {
    voltarComErro('A senha deve ter pelo menos 8 caracteres.', 3);
}

if (!hash_equals($senha, $confirmarSenha)) {
    voltarComErro('As senhas não conferem.', 3);
}

if ($linkedin !== '' && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
    voltarComErro('Informe uma URL válida para o LinkedIn.', 2);
}

$caminhoFoto = null;
$arquivoFotoCriado = null;
if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
    $arquivo = $_FILES['foto_perfil'];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $mensagemUpload = match ($arquivo['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'A foto excede o limite permitido. Envie uma imagem de até 2 MB.',
            UPLOAD_ERR_PARTIAL => 'A foto foi enviada parcialmente. Tente novamente.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'O servidor não conseguiu armazenar temporariamente a foto.',
            default => 'Não foi possível enviar a foto de perfil.',
        };
        voltarComErro($mensagemUpload, 1);
    }

    if ($arquivo['size'] > 2 * 1024 * 1024) {
        voltarComErro('A foto deve ter no máximo 2 MB.', 1);
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    if (!class_exists('finfo')) {
        error_log('Cadastro de candidato: extensão PHP fileinfo indisponível.');
        voltarComErro('O servidor não está configurado para validar fotos. Tente novamente mais tarde.', 1);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipoReal = $finfo->file($_FILES['foto_perfil']['tmp_name']);

    if (!isset($tiposPermitidos[$tipoReal])) {
        voltarComErro('O arquivo selecionado não é uma imagem JPG ou PNG válida.', 1);
    }

    $pastaDestino = __DIR__ . '/uploads/fotos_perfil/';
    if (!is_dir($pastaDestino) && !mkdir($pastaDestino, 0755, true)) {
        error_log('Cadastro de candidato: falha ao criar diretório de fotos: ' . $pastaDestino);
        voltarComErro('Não foi possível preparar o armazenamento da foto.', 1);
    }

    $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$tipoReal];
    $caminhoCompleto = $pastaDestino . $nomeArquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
        error_log('Cadastro de candidato: move_uploaded_file falhou para ' . $caminhoCompleto);
        voltarComErro('Não foi possível salvar a foto de perfil.', 1);
    }

    $arquivoFotoCriado = $caminhoCompleto;
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
    if ($arquivoFotoCriado && is_file($arquivoFotoCriado)) {
        unlink($arquivoFotoCriado);
    }

    if ((int) $e->getCode() === 23000) {
        voltarComErro('Este e-mail já está cadastrado no sistema. Confira o endereço ou faça login.', 1);
    }

    error_log('Cadastro de candidato falhou: ' . $e->getMessage());
    voltarComErro('Não foi possível concluir o cadastro. Os dados foram mantidos; tente novamente.', 3);
}
?>