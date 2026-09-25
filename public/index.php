<?php
// Aqui comeca toda a nossa aplicacao com o Slim Framework!
// Qualquer requisicao que bater na API vai passar primeiro por aqui.

use Slim\Factory\AppFactory;

// Puxa o autoloader do Composer com todas as bibliotecas instaladas (Slim, etc)
require __DIR__ . '/../vendor/autoload.php';

// Cria a nossa aplicacao Slim
$app = AppFactory::create();

// Detecta se estamos rodando dentro de uma pasta (ex: /zt/public no XAMPP)
// para o Slim saber o caminho base e encontrar as rotas /api/... sem erro 404
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

// Se a URL digitada incluir diretamente o 'index.php', adiciona ele no base path
if (str_starts_with($requestUri, $scriptName)) {
    $basePath = $scriptName;
}

if (!empty($basePath) && $basePath !== '/') {
    $app->setBasePath($basePath);
}

// Permite que o Slim entenda dados enviados em JSON no corpo da requisicao (POST/PUT)
$app->addBodyParsingMiddleware();

// Middleware que faz o Slim descobrir qual rota foi chamada
$app->addRoutingMiddleware();

// Se der algum erro, mostra detalhes na tela pra gente conseguir arrumar rapido no desenvolvimento
$app->addErrorMiddleware(true, true, true);

// Carrega todas as rotas da nossa API (produtos, orcamentos, uploads)
require __DIR__ . '/../src/routes.php';

// Roda a aplicacao e devolve a resposta pro navegador ou pro frontend
$app->run();
