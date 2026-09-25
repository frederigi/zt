<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

// Essa classe faz todas as consultas de produtos no banco de dados.
// Assim os controllers nao precisam ficar escrevendo SQL misturado com as regras da rota.
class Produto
{
    /**
     * Pega a lista de produtos cadastrados.
     * Se o usuario filtrou por alguma categoria (ex: "topos-de-bolo"), filtra aqui.
     */
    public static function listar(?string $categoriaSlug = null): array
    {
        $pdo = Connection::getInstance();

        // Consulta os produtos trazendo o nome da categoria e a foto de capa (eh_capa = 1)
        $sql = "SELECT p.id,
                       p.titulo AS nome,
                       c.slug AS categoria_slug,
                       c.nome AS categoria,
                       p.preco_base,
                       p.descricao,
                       p.tamanho,
                       p.destaque,
                       p.visualizacoes,
                       img.url_imagem AS imagem_principal,
                       img.texto_alt AS imagem_alt
                FROM produtos p
                INNER JOIN categorias c ON c.id = p.categoria_id
                LEFT JOIN produto_imagens img ON (img.produto_id = p.id AND img.eh_capa = 1)
                WHERE 1=1";

        $params = [];

        // Se a pessoa passou uma categoria na URL, filtramos por ela
        if (!empty($categoriaSlug)) {
            $sql .= " AND c.slug = :categoria";
            $params[':categoria'] = $categoriaSlug;
        }

        // Deixa os produtos em destaque na frente e depois os mais recentes
        $sql .= " ORDER BY (p.destaque != 'padrao') DESC, p.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Se algum produto ainda nao tiver foto cadastrada, coloca uma foto padrao pra nao ficar feio
        foreach ($produtos as &$item) {
            $item['id'] = (int)$item['id'];
            $item['preco_base'] = (float)$item['preco_base'];
            $item['slug'] = $item['categoria_slug']; // atalho amigavel
            if (empty($item['imagem_principal'])) {
                $item['imagem_principal'] = '/img/logo.jpeg';
                $item['imagem_alt'] = 'Foto do produto ' . $item['nome'];
            }
        }

        return $produtos;
    }

    /**
     * Busca os dados completos de um produto pelo ID dele, junto com todas as fotos.
     */
    public static function detalhar(int $id): ?array
    {
        $pdo = Connection::getInstance();

        // Pega as informacoes do produto
        $sql = "SELECT p.id,
                       p.titulo AS nome,
                       p.descricao,
                       p.tamanho,
                       p.preco_base,
                       p.destaque,
                       p.visualizacoes,
                       c.nome AS categoria,
                       c.slug AS categoria_slug
                FROM produtos p
                INNER JOIN categorias c ON c.id = p.categoria_id
                WHERE p.id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se nao achou nada no banco, retorna nulo pra gente saber que deu 404
        if (!$produto) {
            return null;
        }

        $produto['id'] = (int)$produto['id'];
        $produto['preco_base'] = (float)$produto['preco_base'];

        // Soma mais 1 visualizacao no produto pra cliente saber o que faz sucesso
        $stmtVisita = $pdo->prepare("UPDATE produtos SET visualizacoes = visualizacoes + 1 WHERE id = :id");
        $stmtVisita->execute([':id' => $id]);

        // Agora busca todas as fotos cadastradas pra esse produto (capa primeiro)
        $sqlFotos = "SELECT id, url_imagem, texto_alt, eh_capa, ordem 
                     FROM produto_imagens 
                     WHERE produto_id = :id 
                     ORDER BY eh_capa DESC, ordem ASC";
        $stmtFotos = $pdo->prepare($sqlFotos);
        $stmtFotos->execute([':id' => $id]);
        $fotos = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);

        // Se nao tiver foto, poe uma padrao pra nao quebrar o carrossel do front
        if (empty($fotos)) {
            $fotos = [
                [
                    'id' => 0,
                    'url_imagem' => '/img/logo.jpeg',
                    'texto_alt' => 'Foto ilustrativa do produto ' . $produto['nome'],
                    'eh_capa' => 1,
                    'ordem' => 1
                ]
            ];
        }

        $produto['imagens'] = $fotos;

        return $produto;
    }
}
