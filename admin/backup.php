<?php
// ====================================================================
// BACKUP DO BANCO DE DADOS (EXPORTAÇÃO .SQL)
// ====================================================================
// Permite que a artesã ou o superadmin baixem uma cópia de segurança
// de todas as tabelas e dados para o seu computador com apenas 1 clique.

require_once __DIR__ . '/includes/auth.php';
exigirLogin();

// Se o usuario clicou no botao de baixar o arquivo
if (isset($_GET['download']) && $_GET['download'] === '1') {
    $tabelas = ['categorias', 'produtos', 'produto_imagens', 'orcamentos', 'usuarios'];

    $conteudoSql = "-- ====================================================\n";
    $conteudoSql .= "-- BACKUP DO BANCO DE DADOS: Zebra de Touca\n";
    $conteudoSql .= "-- Gerado em: " . date('d/m/Y \à\s H:i:s') . "\n";
    $conteudoSql .= "-- Projeto Integrador II - UNIVESP\n";
    $conteudoSql .= "-- ====================================================\n\n";
    $conteudoSql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tabelas as $tabela) {
        try {
            // 1. Pega a estrutura da tabela (CREATE TABLE)
            $stmtCreate = $pdo->query("SHOW CREATE TABLE `{$tabela}`");
            $linhaCreate = $stmtCreate->fetch(PDO::FETCH_NUM);
            if ($linhaCreate) {
                $conteudoSql .= "-- ----------------------------------------------------\n";
                $conteudoSql .= "-- Estrutura da tabela `{$tabela}`\n";
                $conteudoSql .= "-- ----------------------------------------------------\n";
                $conteudoSql .= "DROP TABLE IF EXISTS `{$tabela}`;\n";
                $conteudoSql .= $linhaCreate[1] . ";\n\n";
            }

            // 2. Pega todos os registros salvos na tabela
            $stmtDados = $pdo->query("SELECT * FROM `{$tabela}`");
            $linhas = $stmtDados->fetchAll(PDO::FETCH_ASSOC);

            if (count($linhas) > 0) {
                $conteudoSql .= "-- Dados da tabela `{$tabela}`\n";

                foreach ($linhas as $linha) {
                    $colunas = array_keys($linha);
                    $valoresTratados = [];

                    foreach ($linha as $val) {
                        if ($val === null) {
                            $valoresTratados[] = "NULL";
                        } else {
                            // Escapa aspas e barras para nao quebrar no momento de restaurar
                            $valEscapado = addslashes($val);
                            $valoresTratados[] = "'{$valEscapado}'";
                        }
                    }

                    $colunasSql = implode("`, `", $colunas);
                    $valoresSql = implode(", ", $valoresTratados);
                    $conteudoSql .= "INSERT INTO `{$tabela}` (`{$colunasSql}`) VALUES ({$valoresSql});\n";
                }
                $conteudoSql .= "\n";
            }
        } catch (Throwable $e) {
            $conteudoSql .= "-- Erro ao exportar tabela `{$tabela}`: " . $e->getMessage() . "\n\n";
        }
    }

    $conteudoSql .= "SET FOREIGN_KEY_CHECKS=1;\n";

    // Forca o download do arquivo no navegador do usuario
    $nomeArquivo = 'backup_zebra_de_touca_' . date('Y-m-d_H-i-s') . '.sql';

    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
    header('Content-Length: ' . strlen($conteudoSql));
    header('Pragma: no-cache');
    header('Expires: 0');

    echo $conteudoSql;
    exit;
}

$tituloPagina = 'Backup do Banco de Dados';
require_once __DIR__ . '/includes/header_admin.php';
?>

<div class="admin-header-pagina">
    <div>
        <h1>💾 Backup do Banco de Dados</h1>
        <p style="color: var(--admin-suave); font-size: 0.95rem;">
            Exporte uma cópia completa de todos os produtos, fotos, orçamentos e usuários.
        </p>
    </div>
</div>

<div class="card-painel" style="max-width: 700px;">
    <div class="card-painel-cabecalho">
        <h2>Cópia de Segurança</h2>
    </div>

    <div style="padding: 24px;">
        <p style="margin-bottom: 16px; font-size: 0.95rem; line-height: 1.6;">
            Fazer backup regularmente é uma boa prática essencial para garantir que o catálogo da artesã
            fique protegido contra qualquer falha ou imprevisto no servidor.
        </p>

        <div style="background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
            <h3 style="color: var(--admin-sucesso); font-size: 1rem; margin-bottom: 6px;">
                ✅ O que este arquivo inclui:
            </h3>
            <ul style="margin-left: 20px; font-size: 0.9rem; color: #2e7d32; line-height: 1.6;">
                <li>Todas as <strong>Categorias</strong> cadastradas</li>
                <li>Todas as peças e descrições de <strong>Produtos</strong></li>
                <li>Todas as referências da galeria de <strong>Fotos</strong></li>
                <li>Histórico completo de <strong>Orçamentos</strong> recebidos</li>
                <li>Contas e permissões de <strong>Usuários</strong></li>
            </ul>
        </div>

        <a href="backup.php?download=1" class="btn-admin btn-admin-primario" style="padding: 12px 24px; font-size: 1rem;">
            ⬇️ Baixar Arquivo de Backup (.SQL)
        </a>

        <p style="font-size: 0.8rem; color: var(--admin-suave); margin-top: 14px;">
            💡 O arquivo baixado pode ser restaurado a qualquer momento pelo phpMyAdmin do XAMPP ou cPanel.
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
