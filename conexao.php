<?php
$host = 'localhost';
$dbname = 'jobboard_db'; 
$usuario = 'root';
$senha_db = ''; // Mantenha vazio se estiver a usar o padrão do XAMPP/Codespaces

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $senha_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>