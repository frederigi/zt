<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

// Essa classe salva os orcamentos gerados no banco de dados.
// Assim a artesã consegue consultar os pedidos feitos ou ver estatisticas depois.
class Orcamento
{
    /**
     * Salva um novo orcamento no banco e devolve o ID dele.
     */
    public static function salvar(array $dados): int
    {
        $pdo = Connection::getInstance();

        // Se os acabamentos vieram como array (ex: ['laminado_dourado', 'aplique_3d']),
        // junta tudo numa string separada por virgula pra caber na coluna TEXT do banco
        $acabamentosTexto = is_array($dados['acabamentos'] ?? null)
            ? implode(', ', $dados['acabamentos'])
            : (string)($dados['acabamentos'] ?? '');

        $sql = "INSERT INTO orcamentos (
                    produto_id,
                    quantidade,
                    acabamentos,
                    nome_homenageado,
                    idade,
                    total
                ) VALUES (
                    :produto_id,
                    :quantidade,
                    :acabamentos,
                    :nome_homenageado,
                    :idade,
                    :total
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':produto_id'        => (int)($dados['produtoId'] ?? $dados['produto_id'] ?? 0),
            ':quantidade'        => (int)($dados['quantidade'] ?? 1),
            ':acabamentos'       => $acabamentosTexto,
            ':nome_homenageado'  => trim($dados['nome_homenageado'] ?? $dados['nomeHomenageado'] ?? ''),
            ':idade'             => trim($dados['idade'] ?? ''),
            ':total'             => (float)($dados['total'] ?? 0.0),
        ]);

        // Retorna o numero (ID) do orcamento criado
        return (int)$pdo->lastInsertId();
    }
}
