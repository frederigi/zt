<?php
// ====================================================================
// FORMULÁRIO DE PRODUTO (CADASTRAR OU EDITAR)
// ====================================================================
// Este arquivo serve tanto para incluir uma peca nova quanto para editar
// uma que ja foi cadastrada anteriormente.

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$ehEdicao = ($id > 0);

$tituloPagina = $ehEdicao ? 'Editar Produto' : 'Cadastrar Novo Produto';
require_once __DIR__ . '/includes/header_admin.php';

// Busca todas as categorias existentes no banco para preencher o select
$categorias = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome ASC")->fetchAll();

// Dados padrao vazios para cadastro
$produto = [
    'id'           => 0,
    'categoria_id' => '',
    'titulo'       => '',
    'descricao'    => '',
    'tamanho'      => '',
    'preco_base'   => '',
    'destaque'     => 'padrao'
];

// Se for edicao, busca os dados da peca no banco
if ($ehEdicao) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $dadosEncontrados = $stmt->fetch();

    if ($dadosEncontrados) {
        $produto = $dadosEncontrados;
    } else {
        echo "<div class='alerta-msg alerta-erro'>Produto não encontrado!</div>";
        require_once __DIR__ . '/includes/footer_admin.php';
        exit;
    }
}
?>

<div class="admin-header-pagina">
    <div>
        <h1><?= $ehEdicao ? '✏️ Editar Produto' : '➕ Novo Produto' ?></h1>
        <p style="color: var(--admin-suave); font-size: 0.95rem;">
            <?= $ehEdicao ? 'Altere as informações da peça abaixo e clique em Salvar.' : 'Preencha os dados da nova peça para exibir no catálogo.' ?>
        </p>
    </div>
    <div>
        <a href="produtos.php" class="btn-admin btn-admin-secundario">
            &larr; Voltar para a Lista
        </a>
    </div>
</div>

<div class="card-painel" style="max-width: 800px;">
    <div class="card-painel-cabecalho">
        <h2>Dados da Peça</h2>
    </div>

    <form action="produto_salvar.php" method="POST" class="form-admin">
        <!-- Campo oculto guardando o ID (0 se for novo, ou o numero se for edicao) -->
        <input type="hidden" name="id" value="<?= $produto['id'] ?>">

        <div class="form-grupo">
            <label for="titulo">Nome / Título do Produto: *</label>
            <input 
                type="text" 
                id="titulo" 
                name="titulo" 
                class="form-controle" 
                placeholder="Ex: Topo de Bolo Jardim Encantado 3D"
                value="<?= htmlspecialchars($produto['titulo']) ?>"
                required
            >
        </div>

        <div class="form-linha-dupla">
            <div class="form-grupo">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label for="categoria_id" style="margin-bottom: 0;">Categoria ("Pasta"): *</label>
                    <a href="categorias.php" target="_blank" style="font-size: 0.8rem; color: var(--admin-primaria); text-decoration: none;">⚙️ Gerenciar Todas</a>
                </div>

                <div style="display: flex; gap: 6px;">
                    <select id="categoria_id" name="categoria_id" class="form-controle" style="flex: 1;" required>
                        <option value="">-- Escolha a categoria --</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $produto['categoria_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="btn-nova-cat" class="btn-admin btn-admin-primario btn-admin-sm" title="Criar Nova Categoria">➕</button>
                    <button type="button" id="btn-editar-cat" class="btn-admin btn-admin-secundario btn-admin-sm" title="Renomear Categoria Selecionada">✏️</button>
                    <button type="button" id="btn-excluir-cat" class="btn-admin btn-admin-perigo btn-admin-sm" title="Excluir Categoria Selecionada">🗑️</button>
                </div>
                <span style="font-size: 0.78rem; color: var(--admin-suave); margin-top: 4px; display: block;">
                    💡 Use os botões ➕ ✏️ 🗑️ ao lado para criar, editar ou excluir categorias direto daqui.
                </span>
            </div>

            <div class="form-grupo">
                <label for="destaque">Destaque na Vitrine:</label>
                <select id="destaque" name="destaque" class="form-controle">
                    <option value="padrao" <?= $produto['destaque'] === 'padrao' ? 'selected' : '' ?>>Padrão</option>
                    <option value="mais_vendido" <?= $produto['destaque'] === 'mais_vendido' ? 'selected' : '' ?>>⭐ Mais Vendido</option>
                    <option value="lancamento" <?= $produto['destaque'] === 'lancamento' ? 'selected' : '' ?>>🚀 Lançamento</option>
                    <option value="destaque" <?= $produto['destaque'] === 'destaque' ? 'selected' : '' ?>>✨ Destaque Especial</option>
                </select>
            </div>
        </div>

        <div class="form-linha-dupla">
            <div class="form-grupo">
                <label for="preco_base">Preço Inicial / Base (R$): *</label>
                <input 
                    type="number" 
                    step="0.01" 
                    id="preco_base" 
                    name="preco_base" 
                    class="form-controle" 
                    placeholder="Ex: 35.00"
                    value="<?= htmlspecialchars($produto['preco_base']) ?>"
                    required
                >
            </div>

            <div class="form-grupo">
                <label for="tamanho">Dimensões / Tamanho Indicado: *</label>
                <input 
                    type="text" 
                    id="tamanho" 
                    name="tamanho" 
                    class="form-controle" 
                    placeholder="Ex: Bolo de 15cm a 20cm ou 10x10cm"
                    value="<?= htmlspecialchars($produto['tamanho']) ?>"
                    required
                >
            </div>
        </div>

        <div class="form-grupo">
            <label for="descricao">Descrição Completa e Detalhes dos Materiais:</label>
            <textarea 
                id="descricao" 
                name="descricao" 
                rows="4" 
                class="form-controle" 
                placeholder="Conte sobre os papeis utilizados, gramatura, camadas e aplicacoes..."
            ><?= htmlspecialchars($produto['descricao']) ?></textarea>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 10px;">
            <button type="submit" class="btn-admin btn-admin-primario" style="padding: 10px 20px;">
                💾 <?= $ehEdicao ? 'Salvar Alterações' : 'Cadastrar Produto' ?>
            </button>
            <a href="produtos.php" class="btn-admin btn-admin-secundario" style="padding: 10px 16px;">
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- SCRIPT DE MANIPULAÇÃO RÁPIDA DE CATEGORIAS NO DROPDOWN -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectCat = document.getElementById('categoria_id');
    const btnNova = document.getElementById('btn-nova-cat');
    const btnEditar = document.getElementById('btn-editar-cat');
    const btnExcluir = document.getElementById('btn-excluir-cat');

    // 1. CRIAR NOVA CATEGORIA RÁPIDA
    btnNova.addEventListener('click', async () => {
        const nome = prompt('Digite o nome da nova categoria (ex: Batizados, Bodas):');
        if (!nome || !nome.trim()) return;

        try {
            const resp = await fetch('categoria_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'criar', nome: nome.trim() })
            });
            const dados = await resp.json();

            if (dados.sucesso) {
                // Cria a nova opcao dentro do select e ja deixa selecionada
                const novaOpcao = document.createElement('option');
                novaOpcao.value = dados.id;
                novaOpcao.textContent = dados.nome;
                novaOpcao.selected = true;
                selectCat.appendChild(novaOpcao);
                alert(`Categoria "${dados.nome}" criada com sucesso e selecionada!`);
            } else {
                alert(dados.erro || 'Erro ao criar categoria.');
            }
        } catch (e) {
            alert('Erro de conexão ao criar categoria.');
        }
    });

    // 2. RENOMEAR CATEGORIA SELECIONADA
    btnEditar.addEventListener('click', async () => {
        const idSelecionado = selectCat.value;
        if (!idSelecionado) {
            alert('Selecione primeiro uma categoria no dropdown para renomear.');
            return;
        }

        const opcaoAtual = selectCat.options[selectCat.selectedIndex];
        const nomeAntigo = opcaoAtual.textContent.trim();

        const novoNome = prompt('Digite o novo nome para esta categoria:', nomeAntigo);
        if (!novoNome || !novoNome.trim() || novoNome.trim() === nomeAntigo) return;

        try {
            const resp = await fetch('categoria_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'editar', id: idSelecionado, nome: novoNome.trim() })
            });
            const dados = await resp.json();

            if (dados.sucesso) {
                opcaoAtual.textContent = dados.nome;
                alert(`Categoria renomeada para "${dados.nome}" com sucesso!`);
            } else {
                alert(dados.erro || 'Erro ao editar categoria.');
            }
        } catch (e) {
            alert('Erro de conexão ao editar categoria.');
        }
    });

    // 3. EXCLUIR CATEGORIA SELECIONADA
    btnExcluir.addEventListener('click', async () => {
        const idSelecionado = selectCat.value;
        if (!idSelecionado) {
            alert('Selecione primeiro uma categoria no dropdown para excluir.');
            return;
        }

        const opcaoAtual = selectCat.options[selectCat.selectedIndex];
        const nomeAtual = opcaoAtual.textContent.trim();

        if (!confirm(`Tem certeza que deseja excluir a categoria "${nomeAtual}"?`)) return;

        try {
            const resp = await fetch('categoria_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'excluir', id: idSelecionado })
            });
            const dados = await resp.json();

            if (dados.sucesso) {
                opcaoAtual.remove();
                selectCat.value = '';
                alert(`Categoria "${nomeAtual}" excluída com sucesso!`);
            } else {
                alert(dados.erro || 'Não foi possível excluir a categoria.');
            }
        } catch (e) {
            alert('Erro de conexão ao excluir categoria.');
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
