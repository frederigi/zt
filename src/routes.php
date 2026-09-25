<?php

// Aqui a gente mapeia todas as rotas da nossa API.
// Quando o navegador ou o JavaScript chamar uma URL, o Slim sabe exatamente
// qual funcao de qual Controller deve ser acionada.

use App\Controllers\ProdutoController;
use App\Controllers\OrcamentoController;
use App\Controllers\UploadController;

// 1. Vitrine de produtos
// GET /api/produtos (aceita opcionalmente ?categoria=slug pra filtrar)
$app->get('/api/produtos', [ProdutoController::class, 'listar']);

// 2. Detalhes de um produto especifico com sua galeria de fotos
// GET /api/produto/1
$app->get('/api/produto/{id}', [ProdutoController::class, 'detalhar']);

// 3. Calculadora de orcamento (simula o preco em tempo real na tela)
// POST /api/simular_orcamento
$app->post('/api/simular_orcamento', [OrcamentoController::class, 'simular']);

// 4. Salva o orcamento no banco e gera o link prontinho do WhatsApp
// POST /api/registrar_orcamento
$app->post('/api/registrar_orcamento', [OrcamentoController::class, 'registrar']);

// 5. Upload de fotos de produtos para a pasta local uploads/
// POST /api/upload_imagem
$app->post('/api/upload_imagem', [UploadController::class, 'upload']);
