<?php
// ====================================================================
// ATIVAR OU INATIVAR CONTA DE USUÁRIO
// ====================================================================
// Exclusivo para o superadmin bloquear ou desbloquear acessos.

require_once __DIR__ . '/includes/auth.php';
exigirSuperAdmin();

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$acao = trim($_GET['acao'] ?? '');

if ($id <= 0 || !in_array($acao, ['ativar', 'inativar'])) {
    header('Location: usuarios.php');
    exit;
}

// Impede que o superadmin desative o seu proprio usuario por engano
if ($id === (int)$usuarioAtual['id']) {
    $msg = urlencode('Você não pode inativar sua própria conta de administrador.');
    header("Location: usuarios.php?erro={$msg}");
    exit;
}

try {
    $novoStatus = ($acao === 'ativar') ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE usuarios SET ativo = :ativo WHERE id = :id");
    $stmt->execute([':ativo' => $novoStatus, ':id' => $id]);

    $textoAcao = ($novoStatus === 1) ? 'ativado' : 'inativado';
    $msg = urlencode("Usuário {$textoAcao} com sucesso!");
    header("Location: usuarios.php?sucesso={$msg}");
    exit;
} catch (Throwable $e) {
    $msg = urlencode("Erro ao alterar status: " . $e->getMessage());
    header("Location: usuarios.php?erro={$msg}");
    exit;
}
