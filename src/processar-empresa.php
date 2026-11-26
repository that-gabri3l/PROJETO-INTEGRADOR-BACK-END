<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/sessao.php';
require_once __DIR__ . '/Helpers/ValidacoesFormulario.php';

use Infodin\Helpers\ValidacoesFormulario;

exigirTokenCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /empresa/cadastro.php');
    exit;
}

function erroEmpresa(string $mensagem, int $etapa = 1): void
{
    $camposPermitidos = [
        'cnpj', 'nome_empresa', 'setor', 'porte', 'email_corporativo',
        'telefone_empresa', 'site', 'cidade_sede',
    ];
    $dados = [];
    foreach ($camposPermitidos as $campo) {
        $valor = $_POST[$campo] ?? '';
        $dados[$campo] = is_string($valor) ? substr($valor, 0, 500) : '';
    }
    $_SESSION['cadastro_empresa_dados'] = $dados;

    header('Location: /empresa/cadastro.php?erro=' . urlencode($mensagem) . '&etapa=' . max(1, min(3, $etapa)));
    exit;
}

$cnpj = trim($_POST['cnpj'] ?? '');
$nome = trim($_POST['nome_empresa'] ?? '');
$setor = trim($_POST['setor'] ?? '');
$porte = trim($_POST['porte'] ?? '');
$email = strtolower(trim($_POST['email_corporativo'] ?? ''));
$telefone = trim($_POST['telefone_empresa'] ?? '');
$site = trim($_POST['site'] ?? '');
$cidade = trim($_POST['cidade_sede'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';

if ($cnpj === '' || $nome === '' || $setor === '' || $porte === '' || $email === '' || $cidade === '' || $senha === '' || $confirmarSenha === '') {
    $etapaErro = $cnpj === '' || $nome === '' || $setor === '' || $porte === '' ? 1 : ($email === '' || $cidade === '' ? 2 : 3);
    erroEmpresa('Preencha todos os campos obrigatórios.', $etapaErro);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    erroEmpresa('Informe um e-mail corporativo válido.', 2);
}

$cnpjDigitos = ValidacoesFormulario::normalizarCnpj($cnpj);
if ($cnpjDigitos === null) {
    erroEmpresa('Informe um CNPJ com 14 dígitos.', 1);
}

if (!ValidacoesFormulario::telefoneBrasileiro($telefone)) {
    erroEmpresa('Informe um telefone com DDD e 10 ou 11 dígitos.', 2);
}

if (strlen($senha) < 8) {
    erroEmpresa('A senha deve ter pelo menos 8 caracteres.', 3);
}

if (!hash_equals($senha, $confirmarSenha)) {
    erroEmpresa('As senhas não conferem.', 3);
}

if ($site !== '' && !filter_var($site, FILTER_VALIDATE_URL)) {
    erroEmpresa('Informe uma URL válida para o site.', 2);
}

$cnpjFormatado = substr($cnpjDigitos, 0, 2) . '.' . substr($cnpjDigitos, 2, 3) . '.' . substr($cnpjDigitos, 5, 3) . '/' . substr($cnpjDigitos, 8, 4) . '-' . substr($cnpjDigitos, 12, 2);
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    $pdo->beginTransaction();

    $stmtUsuario = $pdo->prepare('INSERT INTO usuarios (tipo, email, senha) VALUES (\'empresa\', :email, :senha)');
    $stmtUsuario->execute(['email' => $email, 'senha' => $senhaHash]);
    $usuarioId = (int) $pdo->lastInsertId();

    $stmtEmpresa = $pdo->prepare(
        'INSERT INTO empresas
            (usuario_id, cnpj, nome, razao_social, setor, porte, email_corporativo, telefone, site, cidade_sede)
         VALUES
            (:usuario_id, :cnpj, :nome, :razao_social, :setor, :porte, :email, :telefone, :site, :cidade)'
    );

    $stmtEmpresa->execute([
        'usuario_id' => $usuarioId,
        'cnpj' => $cnpjFormatado,
        'nome' => $nome,
        'razao_social' => $nome,
        'setor' => $setor,
        'porte' => $porte,
        'email' => $email,
        'telefone' => $telefone !== '' ? $telefone : null,
        'site' => $site !== '' ? $site : null,
        'cidade' => $cidade,
    ]);

    $pdo->commit();
    header('Location: /cadastro/login.php?cadastro=sucesso');
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ((int) $e->getCode() === 23000) {
        erroEmpresa('Este e-mail ou CNPJ já está cadastrado no sistema. Confira os dados ou faça login.', 1);
    }

    error_log('Cadastro de empresa falhou: ' . $e->getMessage());
    erroEmpresa('Não foi possível concluir o cadastro. Os dados foram mantidos; tente novamente.', 3);
}
