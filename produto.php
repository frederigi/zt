<?php
// ====================================================================
// PÁGINA DE DETALHES DO PRODUTO E CALCULADORA DE ORÇAMENTO
// Projeto Integrador - Papelaria Personalizada Zebra de Touca
// ====================================================================

// 1. Conexão com o banco de dados
require_once __DIR__ . '/config/conexao.php';

// Número de WhatsApp da artesã (para envio do orçamento gerado)
// Exemplo: 5511999999999 (código do país + DDD + número)
$numeroWhatsapp = '5511999999999';

// 2. Captura e validação do ID do produto vindo da URL (ex: produto.php?id=1)
$produtoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($produtoId <= 0) {
    header('Location: index.php');
    exit;
}

// 3. Consulta os dados do produto no banco com o nome da categoria
$sqlProduto = "SELECT p.*, c.nome AS categoria_nome, c.slug AS categoria_slug
               FROM produtos p
               INNER JOIN categorias c ON c.id = p.categoria_id
               WHERE p.id = :id";
$stmtProduto = $pdo->prepare($sqlProduto);
$stmtProduto->execute([':id' => $produtoId]);
$produto = $stmtProduto->fetch();

// Se o produto não existir no banco, redireciona para a página inicial
if (!$produto) {
    header('Location: index.php');
    exit;
}

// 4. Incrementa o contador de visualizações (atende ao requisito analítico opcional)
$stmtVisitas = $pdo->prepare("UPDATE produtos SET visualizacoes = visualizacoes + 1 WHERE id = :id");
$stmtVisitas->execute([':id' => $produtoId]);

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

// 5. Busca as fotos do produto cadastradas (de 2 a 4 fotos)
$sqlFotos = "SELECT * FROM produto_imagens 
             WHERE produto_id = :id 
             ORDER BY eh_capa DESC, ordem ASC";
$stmtFotos = $pdo->prepare($sqlFotos);
$stmtFotos->execute([':id' => $produtoId]);
$fotos = $stmtFotos->fetchAll();

// Se não houver nenhuma foto cadastrada, define uma padrão para evitar erros
if (empty($fotos)) {
    $fotos = [
        [
            'url_imagem' => 'img/logo.jpeg',
            'texto_alt' => 'Foto ilustrativa do produto ' . $produto['titulo'],
            'eh_capa' => 1
        ]
    ];
}

// Ajusta a URL de cada foto para a versao local ou externa correta
foreach ($fotos as &$f) {
    $f['url_imagem'] = resolverUrlImagem($f['url_imagem']);
}
unset($f);

// A primeira foto (ou a marcada como capa) é a foto inicial em destaque
$fotoInicial = $fotos[0];

// Define o título da aba do navegador
$tituloPagina = $produto['titulo'];

// 6. Inclui o cabeçalho padrão
require_once __DIR__ . '/includes/header.php';
?>

<!-- TRILHA DE NAVEGAÇÃO (BREADCRUMB - Melhora SEO e Usabilidade) -->
<nav class="migalhas-pao" aria-label="Navegação estrutural">
    <a href="index.php">Início</a> &gt;
    <a href="index.php?categoria=<?= urlencode($produto['categoria_slug']) ?>">
        <?= htmlspecialchars($produto['categoria_nome']) ?>
    </a> &gt;
    <span><?= htmlspecialchars($produto['titulo']) ?></span>
</nav>

<!-- LAYOUT PRINCIPAL EM DUAS COLUNAS: FOTOS + INFORMAÇÕES/CALCULADORA -->
<div class="detalhe-produto-grid">

    <!-- COLUNA 1: GALERIA DE FOTOS INTERATIVA (De 2 a 4 fotos) -->
    <section class="galeria-fotos" aria-label="Fotos do produto">
        
        <!-- Foto Grande em Destaque -->
        <div class="foto-destaque-container">
            <img 
                id="foto-grande-destaque" 
                src="<?= htmlspecialchars($fotoInicial['url_imagem']) ?>" 
                alt="<?= htmlspecialchars($fotoInicial['texto_alt']) ?>" 
                class="foto-destaque-img"
            >
        </div>

        <!-- Miniaturas para troca de foto ao clicar ou navegar -->
        <?php if (count($fotos) > 1): ?>
            <div class="miniaturas-lista" role="tablist" aria-label="Miniaturas de fotos do produto">
                <?php foreach ($fotos as $indice => $foto): ?>
                    <button 
                        type="button" 
                        class="miniatura-item <?= $indice === 0 ? 'ativa' : '' ?>"
                        data-url="<?= htmlspecialchars($foto['url_imagem']) ?>"
                        data-alt="<?= htmlspecialchars($foto['texto_alt']) ?>"
                        aria-label="Ver foto <?= $indice + 1 ?>: <?= htmlspecialchars($foto['texto_alt']) ?>"
                    >
                        <img 
                            src="<?= htmlspecialchars($foto['url_imagem']) ?>" 
                            alt="<?= htmlspecialchars($foto['texto_alt']) ?>" 
                            class="miniatura-img"
                        >
                    </button>
                <?php endforeach; ?>
            </div>
            <p style="font-size: 0.8rem; color: var(--cor-texto-suave); margin-top: 4px;">
                💡 Dica: Clique nas miniaturas para ver mais ângulos e detalhes.
            </p>
        <?php endif; ?>

    </section>

    <!-- COLUNA 2: DETALHES DO PRODUTO E CALCULADORA DE ORÇAMENTO -->
    <section class="produto-detalhes-conteudo" aria-labelledby="titulo-detalhe">

        <header class="produto-info-header">
            <span class="produto-info-categoria"><?= htmlspecialchars($produto['categoria_nome']) ?></span>
            <h1 id="titulo-detalhe" class="produto-info-titulo"><?= htmlspecialchars($produto['titulo']) ?></h1>

            <!-- Badges de Destaque -->
            <?php if ($produto['destaque'] === 'mais_vendido'): ?>
                <span class="selo-destaque selo-mais-vendido" style="position: static; display: inline-block;">⭐ Mais Vendido</span>
            <?php elseif ($produto['destaque'] === 'lancamento'): ?>
                <span class="selo-destaque selo-lancamento" style="position: static; display: inline-block;">🚀 Lançamento</span>
            <?php elseif ($produto['destaque'] === 'destaque'): ?>
                <span class="selo-destaque selo-destaque-geral" style="position: static; display: inline-block;">✨ Destaque</span>
            <?php endif; ?>
        </header>

        <!-- Dimensões e Tamanho (Requisito da Cliente) -->
        <div class="produto-tamanho-destaque">
            <strong>📏 Tamanho / Dimensões:</strong> <?= htmlspecialchars($produto['tamanho']) ?>
        </div>

        <!-- Descrição Completa -->
        <div class="produto-descricao-texto">
            <p><?= nl2br(htmlspecialchars($produto['descricao'])) ?></p>
        </div>

        <!-- ========================================================= -->
        <!-- CALCULADORA DE ORÇAMENTO ESTIMADO (Requisito Especial)      -->
        <!-- ========================================================= -->
        <div class="card-calculadora" data-produto-id="<?= $produto['id'] ?>" data-preco-base="<?= (float)$produto['preco_base'] ?>">
            <h3>🧮 Simular Orçamento</h3>
            <p class="subtitulo-calculadora">
                Preencha a quantidade e acabamentos para estimar o valor antes de falar com a artesã:
            </p>

            <!-- 1. Quantidade de Peças -->
            <div class="calc-grupo">
                <label for="input-quantidade" class="calc-label">Quantidade de peças:</label>
                <div class="controle-quantidade">
                    <button type="button" id="btn-menos-qtd" class="btn-qtd" aria-label="Diminuir quantidade">-</button>
                    <input 
                        type="number" 
                        id="input-quantidade" 
                        class="input-qtd" 
                        value="1" 
                        min="1" 
                        max="500"
                        aria-label="Quantidade de itens"
                    >
                    <button type="button" id="btn-mais-qtd" class="btn-qtd" aria-label="Aumentar quantidade">+</button>
                </div>
            </div>

            <!-- 2. Acabamentos e Opcionais Comuns de Papelaria -->
            <div class="calc-grupo">
                <span class="calc-label">Acabamentos especiais desejados:</span>
                <div class="opcionais-lista">
                    
                    <label class="opcional-item">
                        <input type="checkbox" id="opt-lamicote" class="checkbox-opcional" data-preco="4.00">
                        <span>Papel Lamicote Dourado/Prata em relevo (+ R$ 4,00 un)</span>
                    </label>

                    <label class="opcional-item">
                        <input type="checkbox" id="opt-camadas-3d" class="checkbox-opcional" data-preco="3.00">
                        <span>Apliques extras 3D em camadas (+ R$ 3,00 un)</span>
                    </label>

                    <label class="opcional-item">
                        <input type="checkbox" id="opt-embalagem" class="checkbox-opcional" data-preco="2.50">
                        <span>Embalagem individual com laço pronto (+ R$ 2,50 un)</span>
                    </label>

                </div>
            </div>

            <!-- 3. Informações da Personalização (Opcional) -->
            <div class="calc-grupo">
                <label for="input-personalizacao" class="calc-label">Nome e idade do homenageado (opcional):</label>
                <input 
                    type="text" 
                    id="input-personalizacao" 
                    class="campo-texto-personalizado" 
                    placeholder="Ex: Sophia - 4 anos (Data da festa: 20/10)"
                >
            </div>

            <!-- 4. Total Estimado Atualizado em Tempo Real -->
            <div class="calc-total-bloco">
                <div>
                    <span class="total-label">Orçamento Estimado:</span>
                    <div style="font-size: 0.8rem; color: var(--cor-texto-suave);">
                        Base: R$ <?= number_format($produto['preco_base'], 2, ',', '.') ?> / un
                    </div>
                </div>
                <div class="total-valor-destaque" id="total-orcamento-exibicao">
                    R$ <?= number_format($produto['preco_base'], 2, ',', '.') ?>
                </div>
            </div>

            <!-- 5. Botão de Envio Formatado para o WhatsApp -->
            <a 
                id="btn-enviar-whatsapp" 
                href="#" 
                target="_blank" 
                rel="noopener noreferrer" 
                class="btn-whatsapp-orcamento"
            >
                <svg viewBox="0 0 24 24">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.073.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                </svg>
                Pedir pelo WhatsApp com este Orçamento
            </a>

        </div>

    </section>

</div>

<?php
// 7. Inclui o rodapé padrão (que já carrega assets/js/main.js com a galeria e a calculadora via API REST)
require_once __DIR__ . '/includes/footer.php';
?>
