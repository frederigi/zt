<?php
// ====================================================================
// SALVAR PRODUTO NO BANCO (INSERT / UPDATE)
// ====================================================================
// Processa os dados enviados pelo formulario admin/produto_form.php.

require_once __DIR__ . '/includes/auth.php';
exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: produtos.php');
    exit;
}

$id          = (int)($_POST['id'] ?? 0);
$titulo      = trim($_POST['titulo'] ?? '');
$categoriaId = (int)($_POST['categoria_id'] ?? 0);
$precoBase   = (float)str_replace(',', '.', $_POST['preco_base'] ?? 0);
$tamanho     = trim($_POST['tamanho'] ?? '');
$destaque    = trim($_POST['destaque'] ?? 'padrao');
$descricao   = trim($_POST['descricao'] ?? '');

// Valida campos obrigatorios
if (empty($titulo) || $categoriaId <= 0 || $precoBase <= 0 || empty($tamanho)) {
    $msg = urlencode('Preencha todos os campos obrigatórios corretamente.');
    if ($id > 0) {
        header("Location: produto_form.php?id={$id}&erro={$msg}");
    } else {
        header("Location: produto_form.php?erro={$msg}");
    }
    exit;
}

try {
    if ($id > 0) {
        // Atualiza a peca existente
        $sql = "UPDATE produtos SET 
                    categoria_id = :categoria_id,
                    titulo       = :titulo,
                    descricao    = :descricao,
                    tamanho      = :tamanho,
                    preco_base   = :preco_base,
                    destaque     = :destaque
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':categoria_id' => $categoriaId,
            ':titulo'       => $titulo,
            ':descricao'    => $descricao,
            ':tamanho'      => $tamanho,
            ':preco_base'   => $precoBase,
            ':destaque'     => $destaque,
            ':id'           => $id
        ]);

        $msg = urlencode("Produto '{$titulo}' atualizado com sucesso!");
        header("Location: produtos.php?sucesso={$msg}");
        exit;

    } else {
        // Insere a nova peca
        $sql = "INSERT INTO produtos (categoria_id, titulo, descricao, tamanho, preco_base, destaque) 
                VALUES (:categoria_id, :titulo, :descricao, :tamanho, :preco_base, :destaque)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':categoria_id' => $categoriaId,
            ':titulo'       => $titulo,
            ':descricao'    => $descricao,
            ':tamanho'      => $tamanho,
            ':preco_base'   => $precoBase,
            ':destaque'     => $destaque
        ]);

        $novoId = (int)$pdo->lastInsertId();

        // Redireciona direto para a tela de fotos para a artesa ja subir as imagens da nova peca!
        $msg = urlencode("Produto cadastrado com sucesso! Agora adicione as fotos dele abaixo.");
        header("Location: fotos.php?produto_id={$novoId}&sucesso={$msg}");
        exit;
    }

} catch (Throwable $e) {
    $msg = urlencode("Erro ao salvar produto: " . $e->getMessage());
    header("Location: produtos.php?erro={$msg}");
    exit;
}
