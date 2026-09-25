<?php
// ====================================================================
// LISTAGEM DE PRODUTOS - PAINEL DA ARTESÃ
// ====================================================================
// Aqui a artesã vê todos os produtos cadastrados e escolhe se quer
// editar os dados, gerenciar as fotos ou excluir a peça.

$tituloPagina = 'Gerenciar Produtos';
require_once __DIR__ . '/includes/header_admin.php';

// Consulta todos os produtos cadastrados trazendo a foto marcada como capa
$sql = "SELECT p.*, c.nome AS categoria_nome,
               img.url_imagem AS foto_capa
        FROM produtos p
        INNER JOIN categorias c ON c.id = p.categoria_id
        LEFT JOIN produto_imagens img ON (img.produto_id = p.id AND img.eh_capa = 1)
        ORDER BY p.id DESC";

$produtos = $pdo->query($sql)->fetchAll();

// Mensagens passadas pela URL apos cadastrar, editar ou excluir
$msgSucesso = $_GET['sucesso'] ?? '';
$msgErro = $_GET['erro'] ?? '';
?>

<div class="admin-header-pagina">
    <div>
        <h1>📦 Peças Cadastradas</h1>
        <p style="color: var(--admin-suave); font-size: 0.95rem;">
            Total de <strong><?= count($produtos) ?></strong> produtos no catálogo digital.
        </p>
    </div>
    <div>
        <a href="produto_form.php" class="btn-admin btn-admin-primario">
            ➕ Cadastrar Novo Produto
        </a>
    </div>
</div>

<?php if (!empty($msgSucesso)): ?>
    <div class="alerta-msg alerta-sucesso">
        <?= htmlspecialchars($msgSucesso) ?>
    </div>
<?php endif; ?>

<?php if (!empty($msgErro)): ?>
    <div class="alerta-msg alerta-erro">
        <?= htmlspecialchars($msgErro) ?>
    </div>
<?php endif; ?>

<div class="card-painel">
    <div class="tabela-container">
        <?php if (count($produtos) > 0): ?>
            <table class="tabela-admin">
                <thead>
                    <tr>
                        <th style="width: 60px;">Foto</th>
                        <th>Título da Peça</th>
                        <th>Categoria</th>
                        <th>Tamanho</th>
                        <th>Preço Base</th>
                        <th>Destaque</th>
                        <th>Views</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $p): ?>
                        <?php
                        // Ajusta a URL da foto pra carregar a imagem local ou externa
                        $foto = $p['foto_capa'];
                        if (empty($foto)) {
                            $fotoUrl = '../img/logo.jpeg';
                        } elseif (str_starts_with($foto, 'http')) {
                            $fotoUrl = $foto;
                        } elseif (str_starts_with($foto, '/uploads/')) {
                            $fotoUrl = '../public' . $foto;
                        } else {
                            $fotoUrl = '../' . $foto;
                        }
                        ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="Capa" class="foto-miniatura-tabela">
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($p['titulo']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($p['categoria_nome']) ?></td>
                            <td style="color: var(--admin-suave); font-size: 0.85rem;"><?= htmlspecialchars($p['tamanho']) ?></td>
                            <td style="font-weight: 600;">R$ <?= number_format($p['preco_base'], 2, ',', '.') ?></td>
                            <td>
                                <?php if ($p['destaque'] === 'mais_vendido'): ?>
                                    <span style="background: #fff3cd; color: #856404; font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; font-weight: 600;">⭐ Mais Vendido</span>
                                <?php elseif ($p['destaque'] === 'lancamento'): ?>
                                    <span style="background: #d1ecf1; color: #0c5460; font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; font-weight: 600;">🚀 Lançamento</span>
                                <?php elseif ($p['destaque'] === 'destaque'): ?>
                                    <span style="background: #d4edda; color: #155724; font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; font-weight: 600;">✨ Destaque</span>
                                <?php else: ?>
                                    <span style="color: #999; font-size: 0.8rem;">Padrão</span>
                                <?php endif; ?>
                            </td>
                            <td style="color: var(--admin-suave); font-size: 0.85rem;">👁️ <?= $p['visualizacoes'] ?></td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="fotos.php?produto_id=<?= $p['id'] ?>" class="btn-admin btn-admin-secundario btn-admin-sm" title="Gerenciar fotos desta peça">
                                    📷 Fotos
                                </a>
                                <a href="produto_form.php?id=<?= $p['id'] ?>" class="btn-admin btn-admin-primario btn-admin-sm" title="Editar informações da peça">
                                    ✏️ Editar
                                </a>
                                <a href="produto_excluir.php?id=<?= $p['id'] ?>" class="btn-admin btn-admin-perigo btn-admin-sm" onclick="return confirm('Tem certeza que deseja excluir o produto \'<?= htmlspecialchars(addslashes($p['titulo'])) ?>\'? Todas as fotos dele também serão apagadas.');" title="Excluir produto">
                                    🗑️
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 30px; text-align: center; color: var(--admin-suave);">
                Nenhum produto cadastrado ainda. Clique no botão acima para adicionar a primeira peça!
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
