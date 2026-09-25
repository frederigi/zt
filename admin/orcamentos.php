<?php
// ====================================================================
// LISTAGEM DE ORÇAMENTOS RECEBIDOS
// ====================================================================
// Aqui a artesã consegue ver todos os pedidos e orcamentos simulados
// pelos clientes no catalogo virtual.

$tituloPagina = 'Orçamentos Recebidos';
require_once __DIR__ . '/includes/header_admin.php';

// Busca todos os orcamentos ordenando pelos mais recentes
$sql = "SELECT o.*, p.titulo AS produto_nome, p.preco_base AS produto_preco_base
        FROM orcamentos o
        INNER JOIN produtos p ON p.id = o.produto_id
        ORDER BY o.id DESC";

$orcamentos = $pdo->query($sql)->fetchAll();
?>

<div class="admin-header-pagina">
    <div>
        <h1>💰 Orçamentos Recebidos</h1>
        <p style="color: var(--admin-suave); font-size: 0.95rem;">
            Histórico de orçamentos gerados pelos clientes no site (Total: <strong><?= count($orcamentos) ?></strong>).
        </p>
    </div>
</div>

<div class="card-painel">
    <div class="tabela-container">
        <?php if (count($orcamentos) > 0): ?>
            <table class="tabela-admin">
                <thead>
                    <tr>
                        <th style="width: 70px;"># Orç.</th>
                        <th>Data / Hora</th>
                        <th>Peça Solicitada</th>
                        <th>Qtd.</th>
                        <th>Acabamentos / Opcionais</th>
                        <th>Homenageado / Idade</th>
                        <th>Total</th>
                        <th style="text-align: right;">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orcamentos as $orc): ?>
                        <tr>
                            <td><strong>#<?= $orc['id'] ?></strong></td>
                            <td style="color: var(--admin-suave); font-size: 0.85rem; white-space: nowrap;">
                                <?= date('d/m/Y H:i', strtotime($orc['criado_em'])) ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($orc['produto_nome']) ?></strong>
                            </td>
                            <td><?= $orc['quantidade'] ?> un.</td>
                            <td style="font-size: 0.85rem; color: #444;">
                                <?= !empty($orc['acabamentos']) ? htmlspecialchars($orc['acabamentos']) : '<span style="color:#aaa;">Nenhum</span>' ?>
                            </td>
                            <td>
                                <?php if (!empty($orc['nome_homenageado'])): ?>
                                    <strong><?= htmlspecialchars($orc['nome_homenageado']) ?></strong>
                                    <?php if (!empty($orc['idade'])): ?>
                                        <span style="color: var(--admin-suave); font-size: 0.85rem;">(<?= htmlspecialchars($orc['idade']) ?>)</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#aaa;">Não informado</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: 700; color: var(--admin-sucesso); white-space: nowrap;">
                                R$ <?= number_format($orc['total'], 2, ',', '.') ?>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <?php
                                // Monta uma mensagem amigavel caso a artesa queira puxar conversa com o cliente
                                $textoWhats = "Olá! Vi seu orçamento nº #{$orc['id']} para {$orc['produto_nome']}. Podemos confirmar?";
                                $linkWhats = "https://wa.me/?text=" . rawurlencode($textoWhats);
                                ?>
                                <a href="<?= $linkWhats ?>" target="_blank" class="btn-admin btn-admin-sucesso btn-admin-sm" title="Conversar no WhatsApp">
                                    💬 WhatsApp
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 40px; text-align: center; color: var(--admin-suave);">
                Nenhum orçamento foi gerado no site até o momento.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
