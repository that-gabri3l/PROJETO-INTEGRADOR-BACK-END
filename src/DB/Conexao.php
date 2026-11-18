<?php

namespace Infodin\DB;
use Exception;
use Infodin\Helpers\Utils;
use PDO;
use PDOException;

final class Conexao 
{
    private function __construct(){}

    private static PDO $conexao;
    private static string $host = 'localhost';
    private static string $dbname = 'jobboard_db'; 
    private static string $usuario = 'root';
    private static string $senha = 'senacpenha'; // Deixar vazio caso usar XAMPP

    public static function getConexao(): PDO
    {
        if (!isset(self::$conexao)) {
            try {
                self::$conexao = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8mb4",
                    self::$usuario,
                    self::$senha
                );

                self::$conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conexao->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                Utils::errorLog($e);

                throw new Exception(
                    "Erro ao conectar ao banco de dados. Contate o suporte.",
                    0,
                    $e
                );
            } 
        }

        return self::$conexao;
    }
}