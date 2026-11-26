<?php

namespace Infodin\Helpers;

use DateTimeImmutable;

final class ValidacoesFormulario
{
    public static function telefoneBrasileiro(string $telefone): bool
    {
        $telefone = trim($telefone);

        return $telefone === ''
            || preg_match('/\A(?:[0-9]{2}|\([0-9]{2}\)) ?[0-9]{4,5}-?[0-9]{4}\z/', $telefone) === 1;
    }

    public static function normalizarCnpj(string $cnpj): ?string
    {
        $cnpj = trim($cnpj);

        if (preg_match('/\A[0-9]{14}\z/', $cnpj) === 1) {
            return $cnpj;
        }

        if (preg_match('/\A[0-9]{2}\.[0-9]{3}\.[0-9]{3}\/[0-9]{4}-[0-9]{2}\z/', $cnpj) !== 1) {
            return null;
        }

        return str_replace(['.', '/', '-'], '', $cnpj);
    }

    public static function dataNascimentoValida(string $dataNascimento): bool
    {
        if ($dataNascimento === '') {
            return true;
        }

        if (preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $dataNascimento) !== 1) {
            return false;
        }

        $data = DateTimeImmutable::createFromFormat('!Y-m-d', $dataNascimento);
        $erros = DateTimeImmutable::getLastErrors();

        return $data !== false
            && $data->format('Y-m-d') === $dataNascimento
            && ($erros === false || ($erros['warning_count'] === 0 && $erros['error_count'] === 0))
            && $dataNascimento <= (new DateTimeImmutable('today'))->format('Y-m-d');
    }
}
