<?php
// ====================================================================
// PÁGINA PRINCIPAL - CATÁLOGO PÚBLICO (INDEX)
// Projeto Integrador - Papelaria Personalizada Zebra de Touca
// ====================================================================

// 1. Conexão com o banco de dados
require_once __DIR__ . '/config/conexao.php';

// Define o título que aparecerá na aba do navegador
$tituloPagina = 'Catálogo de Produtos';

// 2. Captura dos filtros vindos da URL (se houver)
// Exemplo: index.php?categoria=topos-de-bolo ou index.php?busca=borboleta
$categoriaFiltro = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
$buscaFiltro     = isset($_GET['busca'])     ? trim($_GET['busca'])     : '';

// Funcao simples pra descobrir onde a foto do produto esta guardada:
// se estiver na pasta local /uploads/, arruma o caminho pro navegador achar
function resolverUrlImagem(?string $url): string {
    if (empty($url)) {
        return 'img/logo.jpeg';
    }
    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
        return $url;
    }
    if (str_starts_with($url, '/uploads/')) {
        return 'public' . $url;
    }
    return $url;
}

// 3. Consulta de todas as categorias para montar os botões de "pastas" com contagem
// Usa LEFT JOIN para contar quantos produtos existem em cada categoria
$sqlCategorias = "SELECT c.id, c.nome, c.slug, COUNT(p.id) AS total_produtos
                  FROM categorias c
                  LEFT JOIN produtos p ON p.categoria_id = c.id
                  GROUP BY c.id, c.nome, c.slug
                  ORDER BY c.id ASC";
$stmtCategorias = $pdo->query($sqlCategorias);
$categorias = $stmtCategorias->fetchAll();

// Total geral de produtos cadastrados
$totalGeralProdutos = 0;
foreach ($categorias as $cat) {
    $totalGeralProdutos += (int)$cat['total_produtos'];
}

// 4. Montagem da consulta dos produtos com filtros dinâmicos
// Usamos prepared statements (com parâmetros :nome) para proteger contra SQL Injection
$sqlProdutos = "SELECT p.*, c.nome AS categoria_nome, c.slug AS categoria_slug,
                       img.url_imagem AS foto_capa,
                       img.texto_alt AS foto_alt
                FROM produtos p
                INNER JOIN categorias c ON c.id = p.categoria_id
                -- Busca apenas a foto marcada como capa (eh_capa = 1)
                LEFT JOIN produto_imagens img ON (img.produto_id = p.id AND img.eh_capa = 1)
                WHERE 1=1";

$parametros = [];

// Se o usuário clicou em uma categoria específica
if (!empty($categoriaFiltro)) {
    $sqlProdutos .= " AND c.slug = :categoria";
    $parametros[':categoria'] = $categoriaFiltro;
}

// Se o usuário digitou algum termo de busca
if (!empty($buscaFiltro)) {
    $sqlProdutos .= " AND (p.titulo LIKE :busca OR p.descricao LIKE :busca)";
    $parametros[':busca'] = '%' . $buscaFiltro . '%';
}

// Ordenação: primeiro os produtos com destaque (mais vendidos, destaques, lançamentos), depois os mais novos
$sqlProdutos .= " ORDER BY (p.destaque != 'padrao') DESC, p.id DESC";

$stmtProdutos = $pdo->prepare($sqlProdutos);
$stmtProdutos->execute($parametros);
$produtos = $stmtProdutos->fetchAll();

// 5. Inclui o cabeçalho padrão (com barra de acessibilidade e logotipo)
require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNER DE APRESENTAÇÃO DO ATELIÊ -->
<section class="banner-apresentacao" aria-labelledby="titulo-banner">
    <h1 id="titulo-banner">Catálogo Digital Zebra de Touca</h1>
    <p>
        Papelaria afetiva e personalizada para aniversários, batizados, casamentos e celebrações.
        Escolha uma pasta abaixo para navegar pelos modelos já feitos e calcule seu orçamento!
    </p>
</section>

<!-- SEÇÃO DE FILTROS E PESQUISA (ESTILO PASTAS) -->
<section class="secao-filtros" aria-label="Filtros do Catálogo">

    <!-- Formulário de Busca por Nome/Tema -->
    <form action="index.php" method="GET" class="barra-pesquisa-form" role="search">
        <?php if (!empty($categoriaFiltro)): ?>
            <!-- Mantém a categoria atual se estiver pesquisando dentro dela -->
            <input type="hidden" name="categoria" value="<?= htmlspecialchars($categoriaFiltro) ?>">
        <?php endif; ?>
        <input 
            type="text" 
            name="busca" 
            class="campo-busca" 
            placeholder="Buscar por tema (ex: Safari, Borboleta, Flores)..." 
            value="<?= htmlspecialchars($buscaFiltro) ?>"
            aria-label="Buscar produtos no catálogo"
        >
        <button type="submit" class="btn-buscar">Buscar</button>
    </form>

    <!-- Abas / Pastas de Categorias -->
    <nav aria-label="Categorias de produtos">
        <ul class="abas-categorias">
            <li class="aba-item">
                <a href="index.php<?= !empty($buscaFiltro) ? '?busca=' . urlencode($buscaFiltro) : '' ?>" 
                   class="<?= empty($categoriaFiltro) ? 'ativa' : '' ?>">
                    📁 Todas as Peças 
                    <span class="contador-badge"><?= $totalGeralProdutos ?></span>
                </a>
            </li>

            <?php foreach ($categorias as $cat): ?>
                <?php
                // Monta o link preservando a busca se ela existir
                $linkUrl = 'index.php?categoria=' . urlencode($cat['slug']);
                if (!empty($buscaFiltro)) {
                    $linkUrl .= '&busca=' . urlencode($buscaFiltro);
                }
                $estaAtiva = ($categoriaFiltro === $cat['slug']);
                ?>
                <li class="aba-item">
                    <a href="<?= $linkUrl ?>" class="<?= $estaAtiva ? 'ativa' : '' ?>">
                        <?= htmlspecialchars($cat['nome']) ?>
                        <span class="contador-badge"><?= $cat['total_produtos'] ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</section>

<!-- GRADE DE PRODUTOS -->
<section aria-label="Lista de Produtos">
    <div class="grid-produtos">

        <?php if (count($produtos) > 0): ?>
            <?php foreach ($produtos as $item): ?>
                <?php
                // Se a imagem de capa for vazia, define uma imagem padrao segura
                $fotoOriginal = !empty($item['foto_capa']) ? $item['foto_capa'] : 'img/logo.jpeg';
                $urlFoto = resolverUrlImagem($fotoOriginal);
                $altFoto = !empty($item['foto_alt']) ? $item['foto_alt'] : 'Foto do produto ' . $item['titulo'];
                ?>
                <article class="card-produto">
                    
                    <!-- Imagem de Capa do Produto com Acessibilidade (alt text) -->
                    <div class="card-imagem-container">
                        <img 
                            src="<?= htmlspecialchars($urlFoto) ?>" 
                            alt="<?= htmlspecialchars($altFoto) ?>" 
                            class="card-imagem"
                            loading="lazy"
                        >
                        
                        <!-- Badges de Destaque -->
                        <?php if ($item['destaque'] === 'mais_vendido'): ?>
                            <span class="selo-destaque selo-mais-vendido">⭐ Mais Vendido</span>
                        <?php elseif ($item['destaque'] === 'lancamento'): ?>
                            <span class="selo-destaque selo-lancamento">🚀 Lançamento</span>
                        <?php elseif ($item['destaque'] === 'destaque'): ?>
                            <span class="selo-destaque selo-destaque-geral">✨ Destaque</span>
                        <?php endif; ?>
                    </div>

                    <!-- Informações do Produto -->
                    <div class="card-corpo">
                        <span class="card-categoria"><?= htmlspecialchars($item['categoria_nome']) ?></span>
                        <h2 class="card-titulo"><?= htmlspecialchars($item['titulo']) ?></h2>
                        <p class="card-tamanho">📏 <?= htmlspecialchars($item['tamanho']) ?></p>
                        
                        <!-- Rodapé do Card com Preço e Ação -->
                        <div class="card-rodape">
                            <div>
                                <span class="card-preco-label">A partir de</span>
                                <span class="card-preco-valor">R$ <?= number_format($item['preco_base'], 2, ',', '.') ?></span>
                            </div>
                            <a href="produto.php?id=<?= $item['id'] ?>" class="btn-ver-detalhes">
                                Ver Modelos
                            </a>
                        </div>
                    </div>

                </article>
            <?php endforeach; ?>

        <?php else: ?>
            <!-- Mensagem caso a busca ou categoria não retorne produtos -->
            <div class="sem-resultados">
                <h3>Nenhum produto encontrado 🦓</h3>
                <p>Não encontramos produtos com os filtros selecionados.</p>
                <a href="index.php" class="btn-ver-detalhes">Ver Todo o Catálogo</a>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php
// 6. Inclui o rodapé padrão com fechamento das tags e scripts
require_once __DIR__ . '/includes/footer.php';
?>
