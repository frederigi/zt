<?php
// ====================================================================
// EXEMPLO DE ARQUIVO DE CONEXAO COM O BANCO DE DADOS (PDO)
// Copie este arquivo para "conexao.php" e preencha com os valores reais.
// O arquivo "conexao.php" NAO deve ser commitado (veja .gitignore).
// ====================================================================

$host = 'localhost';
$banco = 'papelaria_catalogo';
$usuario = 'SEU_USUARIO_AQUI';
$senha = 'SUA_SENHA_AQUI';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$banco};charset={$charset}";

$opcoes = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $usuario, $senha, $opcoes);
} catch (PDOException $erro) {
    die("Erro ao conectar ao banco de dados: " . $erro->getMessage());
}