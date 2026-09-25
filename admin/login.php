<?php
// ====================================================================
// TELA DE LOGIN DO PAINEL DA ARTESÃ
// ====================================================================
// Aqui a artesã ou o superadmin digitam usuario e senha para entrar.

require_once __DIR__ . '/includes/auth.php';

// Se a pessoa ja estiver logada, manda direto pro painel sem pedir senha de novo
if (!empty($_SESSION['usuario_logado'])) {
    header('Location: index.php');
    exit;
}

$mensagemErro = '';

// Quando o formulario for enviado (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioDigitado = trim($_POST['usuario'] ?? '');
    $senhaDigitada   = trim($_POST['senha'] ?? '');

    if (empty($usuarioDigitado) || empty($senhaDigitada)) {
        $mensagemErro = 'Por favor, preencha o usuário e a senha.';
    } else {
        try {
            // Busca o usuario no banco de dados
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1");
            $stmt->execute([':usuario' => $usuarioDigitado]);
            $usuario = $stmt->fetch();

            // Confere se o usuario existe e se a senha confere com o hash seguro
            if ($usuario && password_verify($senhaDigitada, $usuario['senha'])) {
                // Confere se o usuario esta ativo
                if ((int)$usuario['ativo'] !== 1) {
                    $mensagemErro = 'Sua conta está inativada. Fale com o Administrador do Sistema.';
                } else {
                    // Guarda os dados na sessao e entra no painel
                    $_SESSION['usuario_logado'] = [
                        'id'      => (int)$usuario['id'],
                        'nome'    => $usuario['nome'],
                        'usuario' => $usuario['usuario'],
                        'perfil'  => $usuario['perfil'],
                    ];

                    header('Location: index.php');
                    exit;
                }
            } else {
                $mensagemErro = 'Usuário ou senha incorretos.';
            }
        } catch (Throwable $e) {
            $mensagemErro = 'Erro ao conectar no banco: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - Painel da Artesã</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <div class="login-container">
        <div class="card-login">
            <div class="card-login-header">
                <img src="../img/logo.jpeg" alt="Zebra de Touca" style="max-height: 95px; width: auto; border-radius: 8px; margin-bottom: 12px; object-fit: contain;">
                <h1 style="font-size: 1.2rem; color: var(--admin-primaria); font-weight: 700;">Painel da Artesã</h1>
                <p style="color: var(--admin-suave); font-size: 0.85rem;">Acesso Restrito</p>
            </div>

            <?php if (!empty($mensagemErro)): ?>
                <div class="alerta-msg alerta-erro">
                    <?= htmlspecialchars($mensagemErro) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-grupo">
                    <label for="usuario">Usuário:</label>
                    <input 
                        type="text" 
                        id="usuario" 
                        name="usuario" 
                        class="form-controle" 
                        placeholder="Ex: artesa ou superadmin"
                        value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                        required 
                        autofocus
                    >
                </div>

                <div class="form-grupo">
                    <label for="senha">Senha:</label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        class="form-controle" 
                        placeholder="Digite sua senha"
                        required
                    >
                </div>

                <button type="submit" class="btn-admin btn-admin-primario" style="width: 100%; justify-content: center; padding: 12px; margin-top: 8px;">
                    Entrar no Painel
                </button>
            </form>

            <div style="text-align: center; margin-top: 20px; font-size: 0.85rem;">
                <a href="../index.php" style="color: var(--admin-suave); text-decoration: none;">&larr; Voltar para o Catálogo Público</a>
            </div>
        </div>
    </div>

</body>
</html>
