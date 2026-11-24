<?php

function tokenRecuperacaoTemFormatoValido($token): bool
{
    return is_string($token) && preg_match('/\A[a-f0-9]{64}\z/D', $token) === 1;
}

function buscarRecuperacaoAtiva(PDO $pdo, string $tokenHash, bool $bloquear = false): ?array
{
    $sql = 'SELECT r.id, r.usuario_id, r.candidato_id
            FROM recuperacoes_senha r
            LEFT JOIN usuarios u
                ON u.id = r.usuario_id AND u.ativo = 1
            LEFT JOIN candidatos c
                ON c.id = r.candidato_id AND c.usuario_id IS NULL
            WHERE r.token_hash = :token_hash
              AND r.usado_em IS NULL
              AND r.expira_em > NOW()
              AND (
                    (r.usuario_id IS NOT NULL AND u.id IS NOT NULL)
                    OR (r.candidato_id IS NOT NULL AND c.id IS NOT NULL)
              )
            LIMIT 1';

    if ($bloquear) {
        $sql .= ' FOR UPDATE';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['token_hash' => $tokenHash]);
    $recuperacao = $stmt->fetch(PDO::FETCH_ASSOC);

    return $recuperacao ?: null;
}
