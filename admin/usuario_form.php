<?php
// ====================================================================
// FORMULÁRIO DE USUÁRIO (CADASTRAR OU EDITAR)
// ====================================================================
// Exclusivo para o superadmin gerenciar credenciais de acesso.

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$ehEdicao = ($id > 0);

$tituloPagina = $ehEdicao ? 'Editar Usuário' : 'Novo Usuário';
require_once __DIR__ . '/includes/header_admin.php';
exigirSuperAdmin();

$usuario = [
    'id'      => 0,
    'nome'    => '',
    'usuario' => '',
    'perfil'  => 'artesa',
    'ativo'   => 1
];

if ($ehEdicao) {
    $stmt = $pdo->prepare("SELECT id, nome, usuario, perfil, ativo FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $dadosEncontrados = $stmt->fetch();

    if ($dadosEncontrados) {
        $usuario = $dadosEncontrados;
    } else {
        echo "<div class='alerta-msg alerta-erro'>Usuário não encontrado!</div>";
        require_once __DIR__ . '/includes/footer_admin.php';
        exit;
    }
}

$msgErro = $_GET['erro'] ?? '';
?>

<div class="admin-header-pagina">
    <div>
        <h1><?= $ehEdicao ? '✏️ Editar Usuário' : '➕ Novo Usuário' ?></h1>
        <p style="color: var(--admin-suave); font-size: 0.95rem;">
            <?= $ehEdicao ? 'Edite os dados ou defina uma nova senha para a conta.' : 'Cadastre um novo login para acessar o painel administrativo.' ?>
        </p>
    </div>
    <div>
        <a href="usuarios.php" class="btn-admin btn-admin-secundario">
            &larr; Voltar para Usuários
        </a>
    </div>
</div>

<?php if (!empty($msgErro)): ?>
    <div class="alerta-msg alerta-erro">
        <?= htmlspecialchars($msgErro) ?>
    </div>
<?php endif; ?>

<div class="card-painel" style="max-width: 650px;">
    <div class="card-painel-cabecalho">
        <h2>Dados de Acesso</h2>
    </div>

    <form action="usuario_salvar.php" method="POST" class="form-admin">
        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

        <div class="form-grupo">
            <label for="nome">Nome Completo: *</label>
            <input 
                type="text" 
                id="nome" 
                name="nome" 
                class="form-controle" 
                placeholder="Ex: Maria da Silva"
                value="<?= htmlspecialchars($usuario['nome']) ?>"
                required
            >
        </div>

        <div class="form-linha-dupla">
            <div class="form-grupo">
                <label for="usuario">Login de Usuário: *</label>
                <input 
                    type="text" 
                    id="usuario" 
                    name="usuario" 
                    class="form-controle" 
                    placeholder="Ex: maria.artesa"
                    value="<?= htmlspecialchars($usuario['usuario']) ?>"
                    required
                >
            </div>

            <div class="form-grupo">
                <label for="perfil">Perfil de Acesso: *</label>
                <select id="perfil" name="perfil" class="form-controle" required>
                    <option value="artesa" <?= $usuario['perfil'] === 'artesa' ? 'selected' : '' ?>>Artesã (Catálogo e Orçamentos)</option>
                    <option value="superadmin" <?= $usuario['perfil'] === 'superadmin' ? 'selected' : '' ?>>Super Admin (Acesso Completo)</option>
                </select>
            </div>
        </div>

        <div class="form-grupo">
            <label for="senha">
                Senha de Acesso: <?= $ehEdicao ? '<span style="font-weight: normal; color: var(--admin-suave);">(deixe em branco se não quiser alterar)</span>' : '*' ?>
            </label>
            <input 
                type="password" 
                id="senha" 
                name="senha" 
                class="form-controle" 
                placeholder="<?= $ehEdicao ? 'Digite apenas se for mudar a senha' : 'Mínimo de 4 caracteres' ?>"
                <?= $ehEdicao ? '' : 'required' ?>
            >
        </div>

        <div class="form-grupo">
            <label for="ativo">Status da Conta:</label>
            <select id="ativo" name="ativo" class="form-controle">
                <option value="1" <?= (int)$usuario['ativo'] === 1 ? 'selected' : '' ?>>Ativo (pode fazer login normalmente)</option>
                <option value="0" <?= (int)$usuario['ativo'] === 0 ? 'selected' : '' ?>>Inativo (acesso bloqueado)</option>
            </select>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 10px;">
            <button type="submit" class="btn-admin btn-admin-primario" style="padding: 10px 20px;">
                💾 <?= $ehEdicao ? 'Salvar Alterações' : 'Criar Usuário' ?>
            </button>
            <a href="usuarios.php" class="btn-admin btn-admin-secundario" style="padding: 10px 16px;">
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
