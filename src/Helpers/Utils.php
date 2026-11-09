<?php

class Utils
{
    public static function errorLog(Throwable $e): void
    {
        $dataHora = new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo'));
        $mensagem = "[" . $dataHora->format("Y-m-d H:i:s") . "]\n" .
            "Arquivo: " . $e->getFile() . "\n" .
            "Linha: " . $e->getLine(). "\n" .
            "Mensagem: " . $e->getMessage() . "\n\n";

        file_put_contents(__DIR__ . '/../../logs/erros.log', $mensagem, FILE_APPEND);
    }
}