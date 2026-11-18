<?php
$host = 'localhost';
$dbname = 'infodin'; 
$usuario = 'root';
$senha_db = ''; // Mantenha vazio se estiver a usar o padrão do XAMPP/Codespaces

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $usuario, $senha_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    die('Não foi possível conectar ao banco de dados. Tente novamente mais tarde.');
}
?>