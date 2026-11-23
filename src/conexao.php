<?php
$host = getenv('INFODIN_DB_HOST') ?: 'localhost';
$dbname = getenv('INFODIN_DB_NAME') ?: 'jobboard_bd';
$usuario = getenv('INFODIN_DB_USER') ?: 'root';
$senha_db = getenv('INFODIN_DB_PASS') ?: 'senacpenha';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $usuario, $senha_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    die('Não foi possível conectar ao banco de dados. Tente novamente mais tarde.');
}
?>