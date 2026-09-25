<?php

namespace App\Services;

// Essa classe faz apenas uma coisa: a continha matematica do orcamento.
// Separar isso aqui do resto do codigo e otimo porque fica facil de testar
// sem precisar mexer em banco de dados ou rota da web.
class OrcamentoCalculator
{
    // Tabela com os precos dos adicionais que a cliente pode escolher
    // Coloquei tanto os nomes bonitinhos quanto os IDs dos checkboxes do HTML pra nao dar confusao
    public const TABELA_PRECOS = [
        'laminado_dourado' => 4.00,
        'opt-lamicote'     => 4.00,
        'aplique_3d'       => 3.00,
        'aplicue_3d'       => 3.00, // caso venha escrito com 'c' por engano
        'opt-camadas-3d'   => 3.00,
        'embalagem_laco'   => 2.50,
        'opt-embalagem'    => 2.50,
    ];

    /**
     * Calcula o preco total das pecas com base no preco unitario, quantidade e opcionais.
     *
     * Regra: cada adicional e somado no valor unitario da peca e depois
     * tudo e multiplicado pela quantidade total de pecas.
     */
    public function calcular(float $precoBase, int $quantidade, array $acabamentos): array
    {
        // Nao deixa calcular quantidade zero ou negativa, o minimo e sempre 1 peca
        if ($quantidade < 1) {
            $quantidade = 1;
        }

        // Se o preco base vier negativo por algum motivo estranho, zera ele
        if ($precoBase < 0) {
            $precoBase = 0.0;
        }

        // Soma o valor de todos os adicionais validos selecionados
        $totalExtras = 0.0;

        // Evita somar o mesmo acabamento duas vezes se mandar duplicado
        $acabamentosUnicos = array_unique($acabamentos);

        foreach ($acabamentosUnicos as $item) {
            // Se o item estiver na nossa listinha de precos, a gente soma
            // Se for alguma coisa que nao existe, a gente simplesmente ignora
            if (isset(self::TABELA_PRECOS[$item])) {
                $totalExtras += (float)self::TABELA_PRECOS[$item];
            }
        }

        // Valor final de uma peca com todos os opcionais dela
        $precoUnitarioComExtras = $precoBase + $totalExtras;

        // Valor total do pedido inteiro (unitario x quantidade)
        $total = $precoUnitarioComExtras * $quantidade;

        // Arredonda pra 2 casas decimais pra evitar centavos quebrados tipo 10.50000001
        return [
            'precoBase'  => round($precoBase, 2),
            'extras'     => round($totalExtras, 2),
            'quantidade' => $quantidade,
            'total'      => round($total, 2)
        ];
    }
}
