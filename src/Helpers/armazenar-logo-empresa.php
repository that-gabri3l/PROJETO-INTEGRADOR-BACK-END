<?php

function armazenarLogoEmpresa(array $arquivo): array
{
    if (!isset($arquivo['error']) || !is_int($arquivo['error'])) {
        throw new RuntimeException('Não foi possível processar o arquivo enviado.');
    }

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $mensagensErro = [
            UPLOAD_ERR_INI_SIZE => 'A logo excede o limite permitido. Envie uma imagem de até 2 MB.',
            UPLOAD_ERR_FORM_SIZE => 'A logo excede o limite permitido. Envie uma imagem de até 2 MB.',
            UPLOAD_ERR_PARTIAL => 'A logo foi enviada parcialmente. Tente novamente.',
            UPLOAD_ERR_NO_TMP_DIR => 'O servidor não conseguiu preparar o envio da logo.',
            UPLOAD_ERR_CANT_WRITE => 'O servidor não conseguiu armazenar a logo.',
            UPLOAD_ERR_EXTENSION => 'O envio da logo foi interrompido pelo servidor.',
        ];

        throw new RuntimeException(
            $mensagensErro[$arquivo['error']] ?? 'Não foi possível enviar a logo.'
        );
    }

    $temporario = $arquivo['tmp_name'] ?? null;
    if (!is_string($temporario) || !is_uploaded_file($temporario)) {
        throw new RuntimeException('Não foi possível processar o arquivo enviado.');
    }

    $tamanho = filesize($temporario);
    if ($tamanho === false || $tamanho > 2 * 1024 * 1024) {
        throw new RuntimeException('A logo deve ter no máximo 2 MB.');
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipoReal = $finfo->file($temporario);
    if (!isset($tiposPermitidos[$tipoReal])) {
        throw new RuntimeException('Envie uma imagem JPG ou PNG.');
    }

    $pastaDestino = __DIR__ . '/../uploads/logos_empresas/';
    if (!is_dir($pastaDestino)
        && !mkdir($pastaDestino, 0755, true)
        && !is_dir($pastaDestino)) {
        throw new RuntimeException('Não foi possível preparar o armazenamento da logo.');
    }

    $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$tipoReal];
    $caminho = $pastaDestino . $nomeArquivo;
    if (!move_uploaded_file($temporario, $caminho)) {
        throw new RuntimeException('Não foi possível salvar a logo.');
    }

    return [
        'url' => '/src/uploads/logos_empresas/' . $nomeArquivo,
        'caminho' => $caminho,
    ];
}
