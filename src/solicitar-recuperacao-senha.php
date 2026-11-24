<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/sessao.php';
require_once __DIR__ . '/enviar-email-recuperacao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cadastro/recuperar-senha.php');
    exit;
}

exigirTokenCsrf();

$email = $_POST['email'] ?? '';
$email = is_string($email) ? strtolower(trim($email)) : '';

if ($email === '' || strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../cadastro/recuperar-senha.php?erro=email');
    exit;
}

$baseUrl = getenv('INFODIN_BASE_URL') ?: '';
$pepper = getenv('INFODIN_RECOVERY_PEPPER') ?: '';
$remetente = getenv('INFODIN_MAIL_FROM') ?: '';
$partesBaseUrl = parse_url($baseUrl);
$hostBaseUrl = is_array($partesBaseUrl) ? strtolower($partesBaseUrl['host'] ?? '') : '';
$esquemaBaseUrl = is_array($partesBaseUrl) ? strtolower($partesBaseUrl['scheme'] ?? '') : '';
$hostLocal = in_array($hostBaseUrl, ['localhost', '127.0.0.1', '::1', '[::1]'], true);
$baseUrlValida = is_array($partesBaseUrl)
    && filter_var($baseUrl, FILTER_VALIDATE_URL)
    && in_array($esquemaBaseUrl, ['https', 'http'], true)
    && ($esquemaBaseUrl === 'https' || $hostLocal)
    && !isset($partesBaseUrl['user'])
    && !isset($partesBaseUrl['pass'])
    && !isset($partesBaseUrl['query'])
    && !isset($partesBaseUrl['fragment']);

if (!$baseUrlValida || strlen($pepper) < 32 || !filter_var($remetente, FILTER_VALIDATE_EMAIL) || !function_exists('mail')) {
    error_log('Recuperação de senha: configuração de URL, segredo ou envio de e-mail ausente/inválida.');
    header('Location: ../cadastro/recuperar-senha.php?erro=indisponivel');
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    $ip = 'desconhecido';
}

$emailHash = hash_hmac('sha256', 'email:' . $email, $pepper);
$ipHash = hash_hmac('sha256', 'ip:' . $ip, $pepper);

try {
    $pdo->exec('DELETE FROM recuperacoes_senha WHERE criado_em < DATE_SUB(NOW(), INTERVAL 1 DAY)');

    $limiteStmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM recuperacoes_senha
         WHERE criado_em >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
           AND (email_hash = :email_hash OR ip_hash = :ip_hash)'
    );
    $limiteStmt->execute([
        'email_hash' => $emailHash,
        'ip_hash' => $ipHash,
    ]);

    if ((int) $limiteStmt->fetchColumn() >= 5) {
        header('Location: ../cadastro/recuperar-senha.php?enviado=1');
        exit;
    }

    $usuarioId = null;
    $candidatoId = null;

    $usuarioStmt = $pdo->prepare(
        'SELECT id FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1'
    );
    $usuarioStmt->execute(['email' => $email]);
    $usuario = $usuarioStmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $usuarioId = (int) $usuario['id'];
    } else {
        $candidatoStmt = $pdo->prepare(
            'SELECT id FROM candidatos WHERE email = :email AND usuario_id IS NULL LIMIT 1'
        );
        $candidatoStmt->execute(['email' => $email]);
        $candidato = $candidatoStmt->fetch(PDO::FETCH_ASSOC);

        if ($candidato) {
            $candidatoId = (int) $candidato['id'];
        }
    }

    $token = ($usuarioId !== null || $candidatoId !== null)
        ? bin2hex(random_bytes(32))
        : null;
    $tokenHash = $token !== null ? hash('sha256', $token) : null;

    $registrarStmt = $pdo->prepare(
        'INSERT INTO recuperacoes_senha
            (usuario_id, candidato_id, email_hash, ip_hash, token_hash, expira_em)
         VALUES
            (:usuario_id, :candidato_id, :email_hash, :ip_hash, :token_hash,
             CASE WHEN :tem_token = 1 THEN DATE_ADD(NOW(), INTERVAL 30 MINUTE) ELSE NULL END)'
    );
    $registrarStmt->execute([
        'usuario_id' => $usuarioId,
        'candidato_id' => $candidatoId,
        'email_hash' => $emailHash,
        'ip_hash' => $ipHash,
        'token_hash' => $tokenHash,
        'tem_token' => $token !== null ? 1 : 0,
    ]);

    if ($token !== null) {
        $urlRecuperacao = rtrim($baseUrl, '/')
            . '/cadastro/redefinir-senha.php?token=' . rawurlencode($token);

        try {
            $enviado = enviarEmailRecuperacaoSenha($email, $urlRecuperacao);
            if (!$enviado) {
                error_log('Recuperação de senha: mail() não aceitou o envio da mensagem.');
            }
        } catch (RuntimeException $e) {
            error_log('Recuperação de senha: falha no envio: ' . $e->getMessage());
            $enviado = false;
        }

        if (!$enviado) {
            $invalidarStmt = $pdo->prepare(
                'UPDATE recuperacoes_senha SET usado_em = NOW() WHERE id = :id'
            );
            $invalidarStmt->execute(['id' => (int) $pdo->lastInsertId()]);
        }
    }

    header('Location: ../cadastro/recuperar-senha.php?enviado=1');
    exit;
} catch (PDOException $e) {
    error_log('Recuperação de senha falhou no banco de dados: ' . $e->getMessage());
    header('Location: ../cadastro/recuperar-senha.php?erro=indisponivel');
    exit;
}
