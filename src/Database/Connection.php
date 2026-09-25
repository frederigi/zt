<?php

namespace App\Database;

use PDO;
use PDOException;

// Essa classe cuida de conectar no nosso banco MySQL.
// A gente usa o padrao Singleton aqui só pra nao ficar abrindo uma conexao nova
// a cada consulta, economizando memoria do servidor da hospedagem.
class Connection
{
    // Guarda a conexao aberta pra reutilizar depois
    private static ?PDO $instancia = null;

    // Construtor privado pra ninguem dar "new Connection()" direto
    private function __construct() {}

    // Pega a conexao aberta com o banco. Se ainda nao conectou, conecta agora!
    public static function getInstance(): PDO
    {
        if (self::$instancia === null) {
            $caminhoConfig = __DIR__ . '/../../config/conexao.php';
            $caminhoExemplo = __DIR__ . '/../../config/conexao.example.php';

            // Se o arquivo oficial com as senhas existir, usa ele. Se nao, tenta o de exemplo
            $arquivo = file_exists($caminhoConfig) ? $caminhoConfig : $caminhoExemplo;

            if (!file_exists($arquivo)) {
                throw new PDOException("Arquivo de configuracao do banco nao foi encontrado em config/conexao.php");
            }

            // Puxa as variaveis de configuracao ($host, $banco, $usuario, $senha, etc)
            require $arquivo;

            // Se o arquivo ja criou a variavel $pdo, a gente aproveita direto
            if (isset($pdo) && $pdo instanceof PDO) {
                self::$instancia = $pdo;
            } else {
                // Caso as variaveis estejam soltas, criamos a conexao aqui
                $dsn = "mysql:host={$host};dbname={$banco};charset=utf8mb4";
                $opcoes = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                self::$instancia = new PDO($dsn, $usuario, $senha, $opcoes);
            }
        }

        return self::$instancia;
    }
}
