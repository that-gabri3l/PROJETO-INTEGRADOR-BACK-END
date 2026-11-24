<?php

function enviarEmailRecuperacaoSenha(string $destinatario, string $linkRecuperacao): bool
{
    $remetente = getenv('INFODIN_MAIL_FROM');

    if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('E-mail destinatário inválido.');
    }

    if (!$remetente || !filter_var($remetente, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('O remetente do e-mail não está configurado corretamente.');
    }

    if (!filter_var($linkRecuperacao, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('O link de recuperação não é válido.');
    }

    if (!function_exists('mail')) {
        throw new RuntimeException('A função mail() não está disponível neste servidor.');
    }

    $assunto = 'Redefinicao de senha - Infodin';
    $mensagem = "Recebemos um pedido para redefinir a senha da sua conta Infodin.\n\n"
        . "Use o link abaixo em até 30 minutos:\n"
        . $linkRecuperacao
        . "\n\nSe voce nao solicitou a redefinicao, ignore este e-mail.";

    $cabecalhos = "From: Infodin <{$remetente}>\r\n";
    $cabecalhos .= "MIME-Version: 1.0\r\n";
    $cabecalhos .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $cabecalhos .= "Content-Transfer-Encoding: 8bit\r\n";

    return mail($destinatario, $assunto, $mensagem, $cabecalhos);
}
