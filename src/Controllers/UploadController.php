<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

// Esse controller cuida de receber fotos de novos produtos enviadas pela artesã,
// valida se o arquivo e uma imagem de verdade, confere o peso e salva na pasta uploads.
class UploadController
{
    // Limite maximo de tamanho de arquivo: 5 Megabytes em bytes
    public const TAMANHO_MAXIMO_BYTES = 5 * 1024 * 1024;

    // Extensoes e tipos de imagem aceitos no sistema
    public const TIPOS_PERMITIDOS = [
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Recebe o arquivo enviado via formulario, valida e salva na pasta public/uploads.
     */
    public function upload(Request $request, Response $response): Response
    {
        // Pega os arquivos enviados pelo multipart/form-data
        $arquivos = $request->getUploadedFiles();

        // O campo do formulario precisa se chamar 'imagem'
        $arquivoImagem = $arquivos['imagem'] ?? null;

        if (!$arquivoImagem instanceof UploadedFileInterface || $arquivoImagem->getError() !== UPLOAD_ERR_OK) {
            $erro = json_encode([
                'sucesso' => false,
                'erro' => 'Nenhuma imagem foi enviada ou aconteceu uma falha no upload.'
            ], JSON_UNESCAPED_UNICODE);
            $response->getBody()->write($erro);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Valida o tamanho do arquivo (maximo 5MB)
        if ($arquivoImagem->getSize() > self::TAMANHO_MAXIMO_BYTES) {
            $erro = json_encode([
                'sucesso' => false,
                'erro' => 'A foto e muito pesada! O tamanho maximo permitido e de 5MB.'
            ], JSON_UNESCAPED_UNICODE);
            $response->getBody()->write($erro);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Descobre o tipo real do arquivo (MIME-type)
        $tipoMime = $arquivoImagem->getClientMediaType();
        if (!isset(self::TIPOS_PERMITIDOS[$tipoMime])) {
            $erro = json_encode([
                'sucesso' => false,
                'erro' => 'Formato invalido! So aceitamos fotos em JPG, PNG ou WEBP.'
            ], JSON_UNESCAPED_UNICODE);
            $response->getBody()->write($erro);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $extensao = self::TIPOS_PERMITIDOS[$tipoMime];

        // Cria a pasta public/uploads se ela ainda nao existir no servidor
        $pastaDestino = __DIR__ . '/../../public/uploads';
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0755, true);
        }

        // Gera um nome unico pro arquivo pra nunca sobrescrever a foto de outro produto
        $nomeArquivo = uniqid('prod_', true) . '.' . $extensao;
        $caminhoCompleto = $pastaDestino . DIRECTORY_SEPARATOR . $nomeArquivo;

        // Move a foto temporaria para a pasta final
        $arquivoImagem->moveTo($caminhoCompleto);

        // Devolve o caminho que vai ser gravado no banco de dados e acessado pelo site
        $resultado = [
            'sucesso' => true,
            'url'     => '/uploads/' . $nomeArquivo
        ];

        $response->getBody()->write(json_encode($resultado, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}
