<?php
// ====================================================================
// PÁGINA INICIAL DO PAINEL DA ARTESÃ (DASHBOARD)
// ====================================================================
// Mostra um resumo das pecas cadastradas, estatisticas e orcamentos recentes.

$tituloPagina = 'Início';
require_once __DIR__ . '/includes/header_admin.php';

// 1. Consulta o total de produtos cadastrados
$totalProdutos = (int)$pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();

// 2. Consulta o total de orcamentos gerados pelos clientes
$totalOrcamentos = (int)$pdo->query("SELECT COUNT(*) FROM orcamentos")->fetchColumn();

// 3. Consulta o produto mais visto no catalogo (diferencial de interesse do cliente)
$stmtMaisVisto = $pdo->query("SELECT titulo, visualizacoes FROM produtos ORDER BY visualizacoes DESC LIMIT 1");
$produtoMaisVisto = $stmtMaisVisto->fetch();

// 4. Busca os 5 orcamentos mais recentes recebidos
$sqlRecentes = "SELECT o.id, o.quantidade, o.nome_homenageado, o.total, o.criado_em,
                       p.titulo AS produto_nome
                FROM orcamentos o
                INNER JOIN produtos p ON p.id = o.produto_id
                ORDER BY o.id DESC
                LIMIT 5";
$orcamentosRecentes = $pdo->query($sqlRecentes)->fetchAll();
?>

<!-- CABECALHO DA PAGINA COM BOAS-VINDAS -->
<div class="admin-header-pagina">
    <div>
        <h1>Olá, <?= htmlspecialchars($usuarioAtual['nome']) ?>!</h1>
        <p style="color: var(--admin-suave); font-size: 0.95rem;">
            Aqui está o resumo do seu catálogo e dos orçamentos solicitados pelos clientes.
        </p>
    </div>
    <div>
        <a href="produto_form.php" class="btn-admin btn-admin-primario">
            ➕ Cadastrar Novo Produto
        </a>
    </div>
</div>

<!-- CARDS COM INDICADORES RAPIDOS -->
<div class="grid-indicadores">
    
    <!-- Total de Produtos -->
    <div class="card-indicador">
        <div class="icone-indicador">📦</div>
        <div class="info-indicador">
            <h3>Peças no Catálogo</h3>
            <div class="valor"><?= $totalProdutos ?></div>
        </div>
    </div>

    <!-- Total de Orçamentos -->
    <div class="card-indicador">
        <div class="icone-indicador">💰</div>
        <div class="info-indicador">
            <h3>Orçamentos Gerados</h3>
            <div class="valor"><?= $totalOrcamentos ?></div>
        </div>
    </div>

    <!-- Produto Mais Visualizado -->
    <div class="card-indicador">
        <div class="icone-indicador">⭐</div>
        <div class="info-indicador">
            <h3>Mais Visualizado</h3>
            <div class="valor" style="font-size: 1.1rem; line-height: 1.3;">
                <?= $produtoMaisVisto ? htmlspecialchars($produtoMaisVisto['titulo']) : 'Nenhum ainda' ?>
            </div>
            <?php if ($produtoMaisVisto): ?>
                <span style="font-size: 0.8rem; color: var(--admin-suave);">
                    👁️ <?= $produtoMaisVisto['visualizacoes'] ?> visualizações
                </span>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- TABELA DE ULTIMOS ORCAMENTOS RECEBIDOS -->
<div class="card-painel">
    <div class="card-painel-cabecalho">
        <h2>Últimos Orçamentos Recebidos</h2>
        <a href="orcamentos.php" class="btn-admin btn-admin-secundario btn-admin-sm">
            Ver Todos &rarr;
        </a>
    </div>

    <div class="tabela-container">
        <?php if (count($orcamentosRecentes) > 0): ?>
            <table class="tabela-admin">
                <thead>
                    <tr>
                        <th># Nº</th>
                        <th>Peça Solicitada</th>
                        <th>Quantidade</th>
                        <th>Homenageado / Evento</th>
                        <th>Valor Total</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orcamentosRecentes as $orc): ?>
                        <tr>
                            <td><strong>#<?= $orc['id'] ?></strong></td>
                            <td><?= htmlspecialchars($orc['produto_nome']) ?></td>
                            <td><?= $orc['quantidade'] ?> <?= $orc['quantidade'] > 1 ? 'unidades' : 'unidade' ?></td>
                            <td><?= !empty($orc['nome_homenageado']) ? htmlspecialchars($orc['nome_homenageado']) : '<span style="color:#999;">—</span>' ?></td>
                            <td style="font-weight: 600; color: var(--admin-sucesso);">
                                R$ <?= number_format($orc['total'], 2, ',', '.') ?>
                            </td>
                            <td style="color: var(--admin-suave); font-size: 0.85rem;">
                                <?= date('d/m/Y H:i', strtotime($orc['criado_em'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 30px; text-align: center; color: var(--admin-suave);">
                Nenhum orçamento foi gerado no site ainda.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
