<?php

namespace App\Controllers;

use App\Models\Produto;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Esse controller recebe as chamadas do navegador/frontend sobre produtos
// e devolve as respostas em formato JSON bem mastigadinho.
class ProdutoController
{
    /**
     * Devolve a lista de produtos da vitrine.
     * Pode filtrar por categoria se passar ?categoria=slug na URL.
     */
    public function listar(Request $request, Response $response): Response
    {
        // Pega os parametros que vem depois da interrogacao na URL (ex: ?categoria=topos)
        $queryParams = $request->getQueryParams();
        $categoria = !empty($queryParams['categoria']) ? trim($queryParams['categoria']) : null;

        // Busca os produtos no banco atraves do nosso Model
        $produtos = Produto::listar($categoria);

        // Devolve os dados em formato JSON com status 200 (OK)
        $payload = json_encode($produtos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(200);
    }

    /**
     * Devolve todos os detalhes de um produto especifico, incluindo a galeria de fotos.
     */
    public function detalhar(Request $request, Response $response, array $args): Response
    {
        // Pega o ID que veio na URL (ex: /api/produto/2)
        $id = isset($args['id']) ? (int)$args['id'] : 0;

        if ($id <= 0) {
            $erro = json_encode(['sucesso' => false, 'erro' => 'Codigo do produto invalido.']);
            $response->getBody()->write($erro);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $produto = Produto::detalhar($id);

        // Se nao encontrou o produto no banco, avisa com erro 404 (Nao Encontrado)
        if (!$produto) {
            $erro = json_encode(['sucesso' => false, 'erro' => 'Produto nao encontrado no catalogo.']);
            $response->getBody()->write($erro);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Produto achado com sucesso! Devolve tudo em JSON
        $payload = json_encode($produto, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(200);
    }
}
