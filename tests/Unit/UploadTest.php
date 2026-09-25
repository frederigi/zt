<?php

namespace Tests\Unit;

use App\Controllers\UploadController;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Psr7\UploadedFile;
use Slim\Psr7\Uri;

// Esses testes garantem que ninguem consiga subir arquivos perigosos (como .php ou .exe)
// ou fotos gigantescas que lotem a memoria do nosso servidor.
class UploadTest extends TestCase
{
    private UploadController $controller;
    private StreamFactory $streamFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new UploadController();
        $this->streamFactory = new StreamFactory();
    }

    // Cria uma requisicao simulada contendo uma imagem no campo 'imagem'
    private function criarRequisicaoComArquivo(string $nome, string $mimeType, int $tamanho, int $erro = UPLOAD_ERR_OK): Request
    {
        $conteudo = $this->streamFactory->createStream(str_repeat('A', min($tamanho, 1024)));
        $arquivo = new UploadedFile($conteudo, $nome, $mimeType, $tamanho, $erro);

        $uri = new Uri('http', 'localhost', 80, '/api/upload_imagem');
        $headers = new Headers(['Content-Type' => 'multipart/form-data']);
        $request = new Request('POST', $uri, $headers, [], [], $this->streamFactory->createStream());

        return $request->withUploadedFiles(['imagem' => $arquivo]);
    }

    // Testa se o sistema bloqueia tipos que nao sao imagens permitidas (ex: PDF ou TXT)
    public function testRejeitaTipoInvalido(): void
    {
        $request = $this->criarRequisicaoComArquivo('documento.pdf', 'application/pdf', 1024);
        $response = new Response();

        $resultado = $this->controller->upload($request, $response);

        // Deve recusar com status 400 (Bad Request)
        $this->assertEquals(400, $resultado->getStatusCode());

        $corpo = json_decode((string)$resultado->getBody(), true);
        $this->assertFalse($corpo['sucesso']);
        $this->assertStringContainsString('Formato invalido', $corpo['erro']);
    }

    // Testa se o sistema bloqueia arquivos maiores que 5MB (ex: 6MB)
    public function testRejeitaArquivoMaiorQue5MB(): void
    {
        $seisMegasEmBytes = 6 * 1024 * 1024;
        $request = $this->criarRequisicaoComArquivo('foto_pesada.jpg', 'image/jpeg', $seisMegasEmBytes);
        $response = new Response();

        $resultado = $this->controller->upload($request, $response);

        // Deve recusar com status 400 avisando que passou do tamanho
        $this->assertEquals(400, $resultado->getStatusCode());

        $corpo = json_decode((string)$resultado->getBody(), true);
        $this->assertFalse($corpo['sucesso']);
        $this->assertStringContainsString('tamanho maximo permitido', $corpo['erro']);
    }

    // Testa se a lista de tipos aceita os formatos comuns da web: jpeg, png e webp
    public function testAceitaFormatosPermitidos(): void
    {
        $this->assertArrayHasKey('image/jpeg', UploadController::TIPOS_PERMITIDOS);
        $this->assertArrayHasKey('image/png', UploadController::TIPOS_PERMITIDOS);
        $this->assertArrayHasKey('image/webp', UploadController::TIPOS_PERMITIDOS);

        $this->assertEquals('jpg', UploadController::TIPOS_PERMITIDOS['image/jpeg']);
        $this->assertEquals('png', UploadController::TIPOS_PERMITIDOS['image/png']);
        $this->assertEquals('webp', UploadController::TIPOS_PERMITIDOS['image/webp']);
    }
}
