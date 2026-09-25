<?php
// ====================================================================
// GERENCIAMENTO DE USUÁRIOS (EXCLUSIVO PARA SUPERADMIN)
// ====================================================================
// Permite que o administrador do sistema cadastre novos usuarios,
// edite nomes, altere senhas e ative ou desative contas de acesso.

$tituloPagina = 'Gerenciar Usuários';
require_once __DIR__ . '/includes/header_admin.php';
exigirSuperAdmin();

$msgSucesso = $_GET['sucesso'] ?? '';
$msgErro = $_GET['erro'] ?? '';

// Busca todos os usuarios cadastrados
$usuarios = $pdo->query("SELECT id, nome, usuario, perfil, ativo, criado_em FROM usuarios ORDER BY id ASC")->fetchAll();
?>

<div class="admin-header-pagina">
    <div>
        <h1>👥 Gerenciar Usuários do Painel</h1>
        <p style="color: var(--admin-suave); font-size: 0.95rem;">
            Área exclusiva do Administrador do Sistema para controle de logins e acessos.
        </p>
    </div>
    <div>
        <a href="usuario_form.php" class="btn-admin btn-admin-primario">
            ➕ Novo Usuário
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
        <table class="tabela-admin">
            <thead>
                <tr>
                    <th># ID</th>
                    <th>Nome Completo</th>
                    <th>Login (Usuário)</th>
                    <th>Perfil</th>
                    <th>Status</th>
                    <th>Criado em</th>
                    <th style="text-align: right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><strong>#<?= $u['id'] ?></strong></td>
                        <td><?= htmlspecialchars($u['nome']) ?></td>
                        <td><code><?= htmlspecialchars($u['usuario']) ?></code></td>
                        <td>
                            <?php if ($u['perfil'] === 'superadmin'): ?>
                                <span class="badge-superadmin">⭐ Super Admin</span>
                            <?php else: ?>
                                <span style="background: #e1f5fe; color: #0288d1; font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Artesã</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int)$u['ativo'] === 1): ?>
                                <span style="color: var(--admin-sucesso); font-weight: 600;">● Ativo</span>
                            <?php else: ?>
                                <span style="color: var(--admin-perigo); font-weight: 600;">○ Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--admin-suave); font-size: 0.85rem;">
                            <?= date('d/m/Y', strtotime($u['criado_em'])) ?>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="usuario_form.php?id=<?= $u['id'] ?>" class="btn-admin btn-admin-primario btn-admin-sm" title="Editar dados ou redefinir senha">
                                ✏️ Editar / Senha
                            </a>

                            <!-- Impede de desativar o proprio superadmin logado -->
                            <?php if ($u['id'] !== $usuarioAtual['id']): ?>
                                <?php if ((int)$u['ativo'] === 1): ?>
                                    <a href="usuario_status.php?id=<?= $u['id'] ?>&acao=inativar" class="btn-admin btn-admin-perigo btn-admin-sm" onclick="return confirm('Deseja inativar o acesso de <?= htmlspecialchars(addslashes($u['nome'])) ?>?');" title="Bloquear acesso">
                                        🚫 Inativar
                                    </a>
                                <?php else: ?>
                                    <a href="usuario_status.php?id=<?= $u['id'] ?>&acao=ativar" class="btn-admin btn-admin-sucesso btn-admin-sm" title="Liberar acesso">
                                        ✅ Ativar
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
