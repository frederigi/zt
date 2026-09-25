<?php
// ====================================================================
// GERENCIADOR DE CATEGORIAS ("PASTAS") - PAINEL DA ARTESÃ
// ====================================================================
// Aqui a artesã cria, edita e remove as categorias do catálogo
// (ex: Topos de Bolo, Caixas Cenário, Lembrancinhas, Convites).

$tituloPagina = 'Categorias';
require_once __DIR__ . '/includes/header_admin.php';

$msgSucesso = $_GET['sucesso'] ?? '';
$msgErro = $_GET['erro'] ?? '';

// Se estiver editando uma categoria especifica
$editandoId = isset($_GET['editar']) ? (int)$_GET['editar'] : 0;
$categoriaEditando = null;

if ($editandoId > 0) {
    $stmtEdit = $pdo->prepare("SELECT * FROM categorias WHERE id = :id");
    $stmtEdit->execute([':id' => $editandoId]);
    $categoriaEditando = $stmtEdit->fetch();
}

// Busca todas as categorias e conta quantos produtos tem em cada uma
$sql = "SELECT c.*, COUNT(p.id) AS total_produtos
        FROM categorias c
        LEFT JOIN produtos p ON p.categoria_id = c.id
        GROUP BY c.id, c.nome, c.slug, c.descricao
        ORDER BY c.nome ASC";
$categorias = $pdo->query($sql)->fetchAll();
?>

<div class="admin-header-pagina">
    <div>
        <h1>📁 Categorias do Catálogo ("Pastas")</h1>
        <p style="color: var(--admin-suave); font-size: 0.95rem;">
            Organize os produtos em pastas temáticas para facilitar a busca dos clientes.
        </p>
    </div>
    <div>
        <a href="produtos.php" class="btn-admin btn-admin-secundario">
            &larr; Voltar para Produtos
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

<!-- FORMULÁRIO DE CADASTRAR OU EDITAR CATEGORIA -->
<div class="card-painel" style="max-width: 700px; margin-bottom: 24px;">
    <div class="card-painel-cabecalho">
        <h2><?= $categoriaEditando ? '✏️ Editar Categoria' : '➕ Nova Categoria' ?></h2>
    </div>

    <form action="categoria_salvar.php" method="POST" class="form-admin">
        <input type="hidden" name="id" value="<?= $categoriaEditando['id'] ?? 0 ?>">

        <div class="form-grupo">
            <label for="nome">Nome da Categoria: *</label>
            <input 
                type="text" 
                id="nome" 
                name="nome" 
                class="form-controle" 
                placeholder="Ex: Topos de Bolo ou Lembrancinhas e Caixas"
                value="<?= htmlspecialchars($categoriaEditando['nome'] ?? '') ?>"
                required
                autofocus
            >
        </div>

        <div class="form-grupo">
            <label for="descricao">Descrição Breve (Opcional):</label>
            <input 
                type="text" 
                id="descricao" 
                name="descricao" 
                class="form-controle" 
                placeholder="Ex: Peças em camadas de papel, lamicote e shaker"
                value="<?= htmlspecialchars($categoriaEditando['descricao'] ?? '') ?>"
            >
            <span style="font-size: 0.8rem; color: var(--admin-suave);">
                💡 O link amigável (slug) para o filtro da vitrine será gerado automaticamente.
            </span>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 8px;">
            <button type="submit" class="btn-admin btn-admin-primario">
                💾 <?= $categoriaEditando ? 'Salvar Alterações' : 'Criar Categoria' ?>
            </button>
            <?php if ($categoriaEditando): ?>
                <a href="categorias.php" class="btn-admin btn-admin-secundario">
                    Cancelar Edição
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- LISTA DE CATEGORIAS CADASTRADAS -->
<div class="card-painel">
    <div class="card-painel-cabecalho">
        <h2>Categorias Atuais (<?= count($categorias) ?>)</h2>
    </div>

    <div class="tabela-container">
        <table class="tabela-admin">
            <thead>
                <tr>
                    <th>Nome da Categoria</th>
                    <th>Link Amigável (Slug)</th>
                    <th>Descrição</th>
                    <th>Peças Cadastradas</th>
                    <th style="text-align: right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td>
                            <strong>📁 <?= htmlspecialchars($cat['nome']) ?></strong>
                        </td>
                        <td>
                            <code><?= htmlspecialchars($cat['slug']) ?></code>
                        </td>
                        <td style="color: var(--admin-suave); font-size: 0.85rem;">
                            <?= !empty($cat['descricao']) ? htmlspecialchars($cat['descricao']) : '<span style="color:#aaa;">—</span>' ?>
                        </td>
                        <td>
                            <span style="background: #e8eaf6; color: var(--admin-primaria); font-weight: 700; padding: 3px 8px; border-radius: 6px; font-size: 0.85rem;">
                                <?= $cat['total_produtos'] ?> <?= $cat['total_produtos'] == 1 ? 'peça' : 'peças' ?>
                            </span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="categorias.php?editar=<?= $cat['id'] ?>" class="btn-admin btn-admin-primario btn-admin-sm" title="Editar nome desta categoria">
                                ✏️ Editar
                            </a>

                            <?php if ((int)$cat['total_produtos'] === 0): ?>
                                <a href="categoria_excluir.php?id=<?= $cat['id'] ?>" class="btn-admin btn-admin-perigo btn-admin-sm" onclick="return confirm('Tem certeza que deseja excluir a categoria \'<?= htmlspecialchars(addslashes($cat['nome'])) ?>\'?');" title="Excluir categoria">
                                    🗑️ Excluir
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn-admin btn-admin-secundario btn-admin-sm" style="opacity: 0.6; cursor: not-allowed;" title="Não é possível excluir porque existem <?= $cat['total_produtos'] ?> produtos dentro desta pasta">
                                    🔒 Em uso
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
