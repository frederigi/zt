<?php
// ====================================================================
// TOPO PADRÃO DO PAINEL ADMINISTRATIVO (HEADER ADMIN)
// ====================================================================
// Inclui o menu de navegacao, links rapidos e confere permissoes.

require_once __DIR__ . '/auth.php';
exigirLogin();

$usuarioAtual = usuarioLogado();
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($tituloPagina) ? htmlspecialchars($tituloPagina) . ' - ' : '' ?>Painel da Artesã - Zebra de Touca</title>
    <!-- Folha de estilo propria do painel administrativo -->
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <!-- BARRA SUPERIOR DO PAINEL -->
    <nav class="admin-navbar">
        <div class="admin-nav-container">
            <a href="index.php" class="admin-logo" title="Painel da Artesã">
                <img src="../img/logo.jpeg" alt="Zebra de Touca - Painel" style="height: 42px; width: auto; border-radius: 6px; object-fit: contain;">
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--admin-suave); margin-left: 6px;">Painel</span>
            </a>

            <!-- MENU DE OPCOES DO PAINEL -->
            <ul class="admin-menu">
                <li>
                    <a href="index.php" class="<?= $paginaAtual === 'index.php' ? 'ativo' : '' ?>">📊 Início</a>
                </li>
                <li>
                    <a href="produtos.php" class="<?= in_array($paginaAtual, ['produtos.php', 'produto_form.php', 'fotos.php']) ? 'ativo' : '' ?>">📦 Produtos</a>
                </li>
                <li>
                    <a href="categorias.php" class="<?= $paginaAtual === 'categorias.php' ? 'ativo' : '' ?>">📁 Categorias</a>
                </li>
                <li>
                    <a href="orcamentos.php" class="<?= $paginaAtual === 'orcamentos.php' ? 'ativo' : '' ?>">💰 Orçamentos</a>
                </li>
                <li>
                    <a href="backup.php" class="<?= $paginaAtual === 'backup.php' ? 'ativo' : '' ?>">💾 Backup do Banco</a>
                </li>

                <!-- OPCAO EXCLUSIVA DO SUPERADMIN: GERENCIAR USUARIOS -->
                <?php if (isSuperAdmin()): ?>
                    <li>
                        <a href="usuarios.php" class="<?= in_array($paginaAtual, ['usuarios.php', 'usuario_form.php']) ? 'ativo' : '' ?>">
                            👥 Usuários <span class="badge-superadmin">Admin</span>
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="perfil.php" class="<?= $paginaAtual === 'perfil.php' ? 'ativo' : '' ?>">🔑 Trocar Senha</a>
                </li>
                <li>
                    <a href="../index.php" target="_blank" style="color: var(--admin-suave);">🌐 Ver Site</a>
                </li>
                <li>
                    <a href="logout.php" style="color: var(--admin-perigo);">🚪 Sair</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- CONTAINER DO CONTEUDO DA PAGINA -->
    <main class="admin-conteudo">
