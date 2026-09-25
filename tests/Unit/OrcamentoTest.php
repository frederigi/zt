<?php

namespace Tests\Unit;

use App\Services\OrcamentoCalculator;
use PHPUnit\Framework\TestCase;

// Esses testes garantem que a matematica do orcamento nunca erre as contas,
// mesmo se alguem alterar o codigo no futuro.
class OrcamentoTest extends TestCase
{
    private OrcamentoCalculator $calculadora;

    // Executa antes de cada teste comecar, criando uma calculadora novinha
    protected function setUp(): void
    {
        parent::setUp();
        $this->calculadora = new OrcamentoCalculator();
    }

    // Testa quando a pessoa compra 1 peca simples sem nenhum adicional
    public function testCalculoSemAcabamentos(): void
    {
        // Peça de R$ 6,50, 1 unidade, sem opcionais
        $resultado = $this->calculadora->calcular(6.50, 1, []);

        $this->assertEquals(6.50, $resultado['precoBase']);
        $this->assertEquals(0.00, $resultado['extras']);
        $this->assertEquals(1, $resultado['quantidade']);
        $this->assertEquals(6.50, $resultado['total']);
    }

    // Testa adicionando 1 opcional (ex: laminado dourado que custa R$ 4,00)
    public function testCalculoComUmAcabamento(): void
    {
        // (R$ 6,50 base + R$ 4,00 laminado) * 1 peca = R$ 10,50
        $resultado = $this->calculadora->calcular(6.50, 1, ['laminado_dourado']);

        $this->assertEquals(6.50, $resultado['precoBase']);
        $this->assertEquals(4.00, $resultado['extras']);
        $this->assertEquals(1, $resultado['quantidade']);
        $this->assertEquals(10.50, $resultado['total']);
    }

    // Testa com varios acabamentos e pedindo mais de uma peca (ex: 5 pecas)
    public function testCalculoComMultiplosAcabamentosEQuantidadeMaiorQueUm(): void
    {
        // Base: 10.00
        // Extras: laminado (4.00) + aplique 3d (3.00) + embalagem laço (2.50) = 9.50
        // Unitario com extras: 19.50
        // Quantidade: 5 unidades
        // Total esperado: 19.50 * 5 = 97.50
        $resultado = $this->calculadora->calcular(10.00, 5, [
            'laminado_dourado',
            'aplique_3d',
            'embalagem_laco'
        ]);

        $this->assertEquals(10.00, $resultado['precoBase']);
        $this->assertEquals(9.50, $resultado['extras']);
        $this->assertEquals(5, $resultado['quantidade']);
        $this->assertEquals(97.50, $resultado['total']);
    }

    // Se vier algum acabamento inventado ou que nao existe na tabela, tem que ignorar
    public function testCalculoComAcabamentoInvalidoDeveIgnorar(): void
    {
        // O acabamento 'acabamento_fantasma' nao existe, entao os extras continuam 0.00
        $resultado = $this->calculadora->calcular(6.50, 2, ['acabamento_fantasma', 'outro_invalido']);

        $this->assertEquals(6.50, $resultado['precoBase']);
        $this->assertEquals(0.00, $resultado['extras']);
        $this->assertEquals(2, $resultado['quantidade']);
        $this->assertEquals(13.00, $resultado['total']); // 6.50 * 2 = 13.00
    }
}
