<?php
// ====================================================================
// EXCLUSÃO DE PRODUTO
// ====================================================================
// Remove um produto e suas fotos do banco de dados.

require_once __DIR__ . '/includes/auth.php';
exigirLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: produtos.php');
    exit;
}

try {
    // 1. Verifica se existem orcamentos vinculados a esse produto
    $stmtOrc = $pdo->prepare("SELECT COUNT(*) FROM orcamentos WHERE produto_id = :id");
    $stmtOrc->execute([':id' => $id]);
    $totalOrcamentos = (int)$stmtOrc->fetchColumn();

    if ($totalOrcamentos > 0) {
        $msg = urlencode("Não é possível excluir esta peça porque existem {$totalOrcamentos} orçamento(s) vinculado(s) a ela no histórico.");
        header("Location: produtos.php?erro={$msg}");
        exit;
    }

    // 2. Busca o titulo antes de apagar para mostrar na mensagem
    $stmtNome = $pdo->prepare("SELECT titulo FROM produtos WHERE id = :id");
    $stmtNome->execute([':id' => $id]);
    $nome = $stmtNome->fetchColumn();

    // 3. Exclui o produto (o banco apaga as fotos automaticamente via CASCADE)
    $stmtDelete = $pdo->prepare("DELETE FROM produtos WHERE id = :id");
    $stmtDelete->execute([':id' => $id]);

    $msg = urlencode("Produto '{$nome}' excluído com sucesso!");
    header("Location: produtos.php?sucesso={$msg}");
    exit;

} catch (Throwable $e) {
    $msg = urlencode("Erro ao excluir produto: " . $e->getMessage());
    header("Location: produtos.php?erro={$msg}");
    exit;
}
