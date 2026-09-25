<?php
// ====================================================================
// EXCLUIR CATEGORIA
// ====================================================================
// Remove uma categoria do catálogo, desde que não tenha produtos dentro dela.

require_once __DIR__ . '/includes/auth.php';
exigirLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: categorias.php');
    exit;
}

try {
    // 1. Confere se existem produtos cadastrados dentro desta categoria
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE categoria_id = :id");
    $stmtCount->execute([':id' => $id]);
    $totalProdutos = (int)$stmtCount->fetchColumn();

    if ($totalProdutos > 0) {
        $msg = urlencode("Não é possível excluir esta categoria porque ela possui {$totalProdutos} produto(s) cadastrado(s). Mova ou exclua os produtos antes.");
        header("Location: categorias.php?erro={$msg}");
        exit;
    }

    // 2. Busca o nome da categoria para mostrar na mensagem
    $stmtNome = $pdo->prepare("SELECT nome FROM categorias WHERE id = :id");
    $stmtNome->execute([':id' => $id]);
    $nome = $stmtNome->fetchColumn();

    // 3. Exclui a categoria vazia
    $stmtDelete = $pdo->prepare("DELETE FROM categorias WHERE id = :id");
    $stmtDelete->execute([':id' => $id]);

    $msg = urlencode("Categoria '{$nome}' excluída com sucesso!");
    header("Location: categorias.php?sucesso={$msg}");
    exit;

} catch (Throwable $e) {
    $msg = urlencode("Erro ao excluir categoria: " . $e->getMessage());
    header("Location: categorias.php?erro={$msg}");
    exit;
}
