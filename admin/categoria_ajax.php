<?php
// ====================================================================
// AÇÕES RÁPIDAS DE CATEGORIA VIA AJAX (DROPDOWN)
// ====================================================================
// Permite criar, renomear ou excluir categorias direto da tela
// de cadastro de produtos sem recarregar a pagina.

require_once __DIR__ . '/includes/auth.php';
exigirLogin();

header('Content-Type: application/json; charset=utf-8');

$dados = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$acao  = $dados['acao'] ?? '';

// Funcao simples pra gerar o link amigavel (slug)
function criarSlug(string $texto): string {
    $texto = mb_strtolower($texto, 'UTF-8');
    $mapa = [
        'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ü'=>'u','ç'=>'c',
        'Á'=>'a','À'=>'a','Ã'=>'a','Â'=>'a','É'=>'e','Ê'=>'e','Í'=>'i','Ó'=>'o','Ô'=>'o','Õ'=>'o','Ú'=>'u','Ü'=>'u','Ç'=>'c'
    ];
    $texto = strtr($texto, $mapa);
    $texto = preg_replace('/[^a-z0-9]+/i', '-', $texto);
    return trim($texto, '-');
}

// -------------------------------------------------------------
// 1. CRIAR NOVA CATEGORIA RÁPIDA
// -------------------------------------------------------------
if ($acao === 'criar') {
    $nome = trim($dados['nome'] ?? '');
    if (empty($nome)) {
        echo json_encode(['sucesso' => false, 'erro' => 'O nome da categoria é obrigatório.']);
        exit;
    }

    $slug = criarSlug($nome);

    try {
        // Confere se o slug ja existe
        $check = $pdo->prepare("SELECT id FROM categorias WHERE slug = :slug");
        $check->execute([':slug' => $slug]);
        if ($check->fetch()) {
            $slug .= '-' . rand(1, 99);
        }

        $stmt = $pdo->prepare("INSERT INTO categorias (nome, slug, descricao) VALUES (:nome, :slug, '')");
        $stmt->execute([':nome' => $nome, ':slug' => $slug]);
        $novoId = (int)$pdo->lastInsertId();

        echo json_encode([
            'sucesso' => true,
            'id'      => $novoId,
            'nome'    => $nome
        ]);
        exit;
    } catch (Throwable $e) {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao criar categoria: ' . $e->getMessage()]);
        exit;
    }
}

// -------------------------------------------------------------
// 2. EDITAR/RENOMEAR CATEGORIA
// -------------------------------------------------------------
if ($acao === 'editar') {
    $id = (int)($dados['id'] ?? 0);
    $novoNome = trim($dados['nome'] ?? '');

    if ($id <= 0 || empty($novoNome)) {
        echo json_encode(['sucesso' => false, 'erro' => 'ID e novo nome são obrigatórios.']);
        exit;
    }

    $slug = criarSlug($novoNome);

    try {
        $stmt = $pdo->prepare("UPDATE categorias SET nome = :nome, slug = :slug WHERE id = :id");
        $stmt->execute([':nome' => $novoNome, ':slug' => $slug, ':id' => $id]);

        echo json_encode([
            'sucesso' => true,
            'id'      => $id,
            'nome'    => $novoNome
        ]);
        exit;
    } catch (Throwable $e) {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao editar categoria: ' . $e->getMessage()]);
        exit;
    }
}

// -------------------------------------------------------------
// 3. EXCLUIR CATEGORIA
// -------------------------------------------------------------
if ($acao === 'excluir') {
    $id = (int)($dados['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['sucesso' => false, 'erro' => 'ID da categoria inválido.']);
        exit;
    }

    try {
        // Confere se tem produtos dentro desta categoria
        $check = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE categoria_id = :id");
        $check->execute([':id' => $id]);
        $total = (int)$check->fetchColumn();

        if ($total > 0) {
            echo json_encode([
                'sucesso' => false,
                'erro' => "Não é possível excluir! Esta categoria possui {$total} produto(s) cadastrado(s)."
            ]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = :id");
        $stmt->execute([':id' => $id]);

        echo json_encode(['sucesso' => true]);
        exit;
    } catch (Throwable $e) {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao excluir: ' . $e->getMessage()]);
        exit;
    }
}

echo json_encode(['sucesso' => false, 'erro' => 'Ação não reconhecida.']);
