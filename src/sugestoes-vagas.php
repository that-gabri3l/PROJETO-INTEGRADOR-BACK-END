<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$termoRecebido = $_GET['q'] ?? '';
if (!is_string($termoRecebido)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Termo de busca inválido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$termo = trim($termoRecebido);
$quantidadeCaracteres = preg_match_all('/./us', $termo);
if ($quantidadeCaracteres === false) {
    http_response_code(400);
    echo json_encode(['erro' => 'Termo de busca inválido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($quantidadeCaracteres < 2) {
    echo json_encode(['sugestoes' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($quantidadeCaracteres > 100) {
    http_response_code(400);
    echo json_encode(['erro' => 'O termo de busca excede o limite permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/conexao.php';

$busca = '%' . $termo . '%';
try {
    $stmt = $pdo->prepare(
        "SELECT sugestao AS valor, tipo
         FROM (
             SELECT v.titulo AS sugestao, 'Vaga' AS tipo, 1 AS prioridade
             FROM vagas v
             WHERE v.status = 'ativa' AND v.titulo LIKE :titulo
             UNION
             SELECT e.nome AS sugestao, 'Empresa' AS tipo, 2 AS prioridade
             FROM empresas e
             WHERE e.nome LIKE :empresa_nome
                OR e.razao_social LIKE :empresa_razao_social
             UNION
             SELECT v.area AS sugestao, 'Área' AS tipo, 3 AS prioridade
             FROM vagas v
             WHERE v.status = 'ativa' AND v.area LIKE :area
             UNION
             SELECT v.localizacao AS sugestao, 'Localização' AS tipo, 4 AS prioridade
             FROM vagas v
             WHERE v.status = 'ativa' AND v.localizacao LIKE :localizacao
             UNION
             SELECT v.regime AS sugestao, 'Regime' AS tipo, 5 AS prioridade
             FROM vagas v
             WHERE v.status = 'ativa' AND v.regime LIKE :regime
         ) AS resultados
         GROUP BY sugestao, tipo
         ORDER BY MIN(prioridade), sugestao
         LIMIT 8"
    );
    $stmt->execute([
        'titulo' => $busca,
        'empresa_nome' => $busca,
        'empresa_razao_social' => $busca,
        'area' => $busca,
        'localizacao' => $busca,
        'regime' => $busca,
    ]);

    echo json_encode(
        ['sugestoes' => $stmt->fetchAll(PDO::FETCH_ASSOC)],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
} catch (PDOException $e) {
    error_log('Busca de sugestões de vagas falhou: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Não foi possível carregar sugestões agora.'], JSON_UNESCAPED_UNICODE);
}
