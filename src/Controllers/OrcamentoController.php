<?php

namespace App\Controllers;

use App\Models\Orcamento;
use App\Models\Produto;
use App\Services\OrcamentoCalculator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Esse controller cuida de toda a parte financeira do nosso catalogo:
// simula o valor das pecas com os adicionais e depois gera a mensagem prontinha pro WhatsApp.
class OrcamentoController
{
    // Numero de WhatsApp oficial do atelie (com DDD e codigo do pais)
    // Depois a artesã pode alterar se trocar de chip
    private string $telefoneWhatsapp = '5511999999999';

    /**
     * Faz o calculo do orcamento em tempo real quando o usuario mexe na pagina.
     * Recebe: produtoId, quantidade, acabamentos[] e opcionalmente precoBase.
     */
    public function simular(Request $request, Response $response): Response
    {
        // Pega os dados enviados pelo JavaScript (em formato JSON)
        $dados = (array)$request->getParsedBody();

        $produtoId   = (int)($dados['produtoId'] ?? $dados['produto_id'] ?? 0);
        $quantidade  = (int)($dados['quantidade'] ?? 1);
        $acabamentos = is_array($dados['acabamentos'] ?? null) ? $dados['acabamentos'] : [];

        // Descobre qual e o preco base da peca:
        // se o front ja mandou, usa ele. Se nao mandou mas passou o ID do produto, busca no banco
        $precoBase = isset($dados['precoBase']) ? (float)$dados['precoBase'] : null;

        if ($precoBase === null && $produtoId > 0) {
            $produto = Produto::detalhar($produtoId);
            if ($produto) {
                $precoBase = (float)$produto['preco_base'];
            }
        }

        // Se mesmo assim nao achou nenhum preco, coloca zero pra nao quebrar
        if ($precoBase === null) {
            $precoBase = 0.0;
        }

        // Chama nossa calculadora isolada pra fazer as continhas
        $calculadora = new OrcamentoCalculator();
        $resultado = $calculadora->calcular($precoBase, $quantidade, $acabamentos);

        // Monta a resposta que o front precisa pra atualizar a tela
        $resposta = [
            'sucesso'    => true,
            'precoBase'  => $resultado['precoBase'],
            'extras'     => $resultado['extras'],
            'quantidade' => $resultado['quantidade'],
            'total'      => $resultado['total']
        ];

        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * Salva o orcamento no banco de dados e cria o link direto pro WhatsApp da artesã.
     */
    public function registrar(Request $request, Response $response): Response
    {
        $dados = (array)$request->getParsedBody();

        $produtoId        = (int)($dados['produtoId'] ?? $dados['produto_id'] ?? 0);
        $quantidade       = (int)($dados['quantidade'] ?? 1);
        $acabamentos      = is_array($dados['acabamentos'] ?? null) ? $dados['acabamentos'] : [];
        $nomeHomenageado  = trim($dados['nome_homenageado'] ?? $dados['nomeHomenageado'] ?? '');
        $idade            = trim($dados['idade'] ?? '');
        $totalInformado   = isset($dados['total']) ? (float)$dados['total'] : null;

        // Pega as informacoes do produto pra montar a mensagem bem bonita
        $nomeProduto = 'Produto Personalizado';
        $precoBase = 0.0;

        if ($produtoId > 0) {
            $produto = Produto::detalhar($produtoId);
            if ($produto) {
                $nomeProduto = $produto['nome'];
                $precoBase = (float)$produto['preco_base'];
            }
        }

        // Recalcula o total oficial no servidor por seguranca (pro usuario nao alterar preco no inspecionar elemento)
        $calculadora = new OrcamentoCalculator();
        $calculo = $calculadora->calcular($precoBase, $quantidade, $acabamentos);
        $totalOficial = ($totalInformado !== null && $totalInformado > 0 && $totalInformado === $calculo['total'])
            ? $totalInformado
            : $calculo['total'];

        // Grava na tabela 'orcamentos' do MySQL
        $idOrcamento = Orcamento::salvar([
            'produto_id'       => $produtoId,
            'quantidade'       => $quantidade,
            'acabamentos'      => $acabamentos,
            'nome_homenageado' => $nomeHomenageado,
            'idade'            => $idade,
            'total'            => $totalOficial,
        ]);

        // Mapeia os nomes amigaveis dos acabamentos pra mensagem do WhatsApp
        $nomesAcabamentos = [
            'laminado_dourado' => 'Papel Lamicote Dourado/Prata em relevo',
            'opt-lamicote'     => 'Papel Lamicote Dourado/Prata em relevo',
            'aplique_3d'       => 'Apliques extras 3D em camadas',
            'aplicue_3d'       => 'Apliques extras 3D em camadas',
            'opt-camadas-3d'   => 'Apliques extras 3D em camadas',
            'embalagem_laco'   => 'Embalagem individual com laço pronto',
            'opt-embalagem'    => 'Embalagem individual com laço pronto',
        ];

        // Monta o texto que vai preenchido direto no WhatsApp da cliente
        $textoWhats = "Olá! Gostaria de encomendar na *Zebra de Touca*:\n\n";
        $textoWhats .= "🔖 *Orçamento nº:* #{$idOrcamento}\n";
        $textoWhats .= "📌 *Produto:* {$nomeProduto}\n";
        $textoWhats .= "🔢 *Quantidade:* {$quantidade} " . ($quantidade > 1 ? 'unidades' : 'unidade') . "\n";

        if (!empty($acabamentos)) {
            $textoWhats .= "✨ *Acabamentos escolhidos:*\n";
            foreach ($acabamentos as $opt) {
                $nomeOpt = $nomesAcabamentos[$opt] ?? $opt;
                $textoWhats .= "  • {$nomeOpt}\n";
            }
        }

        if (!empty($nomeHomenageado)) {
            $textoWhats .= "✍️ *Homenageado:* {$nomeHomenageado}";
            if (!empty($idade)) {
                $textoWhats .= " ({$idade})";
            }
            $textoWhats .= "\n";
        }

        $totalFormatado = number_format($totalOficial, 2, ',', '.');
        $textoWhats .= "\n💰 *Orçamento estimado no site:* R$ {$totalFormatado}\n\n";
        $textoWhats .= "Você teria disponibilidade para essa produção?";

        // Cria o link final no padrao universal wa.me
        $linkWhatsapp = "https://wa.me/{$this->telefoneWhatsapp}?text=" . rawurlencode($textoWhats);

        $resposta = [
            'sucesso'       => true,
            'id_orcamento'  => $idOrcamento,
            'link_whatsapp' => $linkWhatsapp,
            'total'         => $totalOficial
        ];

        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}
