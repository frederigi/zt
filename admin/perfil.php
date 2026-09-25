<?php
// ====================================================================
// PERFIL DO USUÁRIO - TROCAR SENHA
// ====================================================================
// Permite que qualquer usuario (artesã ou superadmin) troque sua propria senha.

$tituloPagina = 'Trocar Senha';
require_once __DIR__ . '/includes/header_admin.php';

$mensagemSucesso = '';
$mensagemErro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senhaAtual   = trim($_POST['senha_atual'] ?? '');
    $novaSenha    = trim($_POST['nova_senha'] ?? '');
    $confirmaNova = trim($_POST['confirma_nova'] ?? '');

    $idUsuario = (int)$usuarioAtual['id'];

    if (empty($senhaAtual) || empty($novaSenha) || empty($confirmaNova)) {
        $mensagemErro = 'Preencha todos os campos para alterar a senha.';
    } elseif ($novaSenha !== $confirmaNova) {
        $mensagemErro = 'A nova senha e a confirmação não são iguais.';
    } elseif (strlen($novaSenha) < 4) {
        $mensagemErro = 'A nova senha deve ter pelo menos 4 caracteres.';
    } else {
        try {
            // Busca a senha atual gravada no banco
            $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $idUsuario]);
            $hashBanco = $stmt->fetchColumn();

            // Confere se a senha atual digitada confere com a que esta gravada
            if ($hashBanco && password_verify($senhaAtual, $hashBanco)) {
                // Gera o novo hash seguro e atualiza
                $novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                $stmtUpdate = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
                $stmtUpdate->execute([':senha' => $novoHash, ':id' => $idUsuario]);

                $mensagemSucesso = 'Sua senha foi alterada com sucesso!';
            } else {
                $mensagemErro = 'A senha atual informada está incorreta.';
            }
        } catch (Throwable $e) {
            $mensagemErro = 'Erro ao atualizar a senha: ' . $e->getMessage();
        }
    }
}
?>

<div class="admin-header-pagina">
    <div>
        <h1>🔑 Trocar Minha Senha</h1>
        <p style="color: var(--admin-suave); font-size: 0.95rem;">
            Usuário conectado: <strong><?= htmlspecialchars($usuarioAtual['nome']) ?></strong> (<?= htmlspecialchars($usuarioAtual['usuario']) ?>)
        </p>
    </div>
</div>

<div class="card-painel" style="max-width: 550px;">
    <div class="card-painel-cabecalho">
        <h2>Alterar Senha de Acesso</h2>
    </div>

    <?php if (!empty($mensagemSucesso)): ?>
        <div style="padding: 16px 24px 0;">
            <div class="alerta-msg alerta-sucesso">
                <?= htmlspecialchars($mensagemSucesso) ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensagemErro)): ?>
        <div style="padding: 16px 24px 0;">
            <div class="alerta-msg alerta-erro">
                <?= htmlspecialchars($mensagemErro) ?>
            </div>
        </div>
    <?php endif; ?>

    <form action="perfil.php" method="POST" class="form-admin">
        <div class="form-grupo">
            <label for="senha_atual">Senha Atual:</label>
            <input 
                type="password" 
                id="senha_atual" 
                name="senha_atual" 
                class="form-controle" 
                placeholder="Digite a senha que você usa hoje"
                required
            >
        </div>

        <div class="form-grupo">
            <label for="nova_senha">Nova Senha:</label>
            <input 
                type="password" 
                id="nova_senha" 
                name="nova_senha" 
                class="form-controle" 
                placeholder="Digite sua nova senha (mínimo 4 caracteres)"
                required
            >
        </div>

        <div class="form-grupo">
            <label for="confirma_nova">Confirme a Nova Senha:</label>
            <input 
                type="password" 
                id="confirma_nova" 
                name="confirma_nova" 
                class="form-controle" 
                placeholder="Repita a nova senha exatamente igual"
                required
            >
        </div>

        <button type="submit" class="btn-admin btn-admin-primario" style="padding: 10px 18px;">
            Salvar Nova Senha
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
