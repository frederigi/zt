<?php
// ====================================================================
// SALVAR USUÁRIO (INSERT / UPDATE)
// ====================================================================
// Processa o formulario admin/usuario_form.php pelo superadmin.

require_once __DIR__ . '/includes/auth.php';
exigirSuperAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: usuarios.php');
    exit;
}

$id      = (int)($_POST['id'] ?? 0);
$nome    = trim($_POST['nome'] ?? '');
$usuario = trim($_POST['usuario'] ?? '');
$perfil  = trim($_POST['perfil'] ?? 'artesa');
$ativo   = (int)($_POST['ativo'] ?? 1);
$senha   = trim($_POST['senha'] ?? '');

// Valida campos obrigatorios
if (empty($nome) || empty($usuario)) {
    $msg = urlencode('Nome e login de usuário são obrigatórios.');
    header("Location: usuario_form.php?id={$id}&erro={$msg}");
    exit;
}

try {
    // 1. Confere se o login ja nao esta sendo usado por outro usuario
    $sqlCheck = "SELECT id FROM usuarios WHERE usuario = :usuario AND id != :id";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([':usuario' => $usuario, ':id' => $id]);
    if ($stmtCheck->fetch()) {
        $msg = urlencode("O login '{$usuario}' já está em uso por outra conta. Escolha outro.");
        header("Location: usuario_form.php?id={$id}&erro={$msg}");
        exit;
    }

    if ($id > 0) {
        // EDICAO
        if (!empty($senha)) {
            // Digitou nova senha: atualiza tudo incluindo o novo hash da senha
            if (strlen($senha) < 4) {
                $msg = urlencode('A senha deve ter pelo menos 4 caracteres.');
                header("Location: usuario_form.php?id={$id}&erro={$msg}");
                exit;
            }
            $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nome = :nome, usuario = :usuario, perfil = :perfil, ativo = :ativo, senha = :senha WHERE id = :id";
            $params = [':nome' => $nome, ':usuario' => $usuario, ':perfil' => $perfil, ':ativo' => $ativo, ':senha' => $hashSenha, ':id' => $id];
        } else {
            // Nao mexeu na senha: mantem a que ja estava gravada
            $sql = "UPDATE usuarios SET nome = :nome, usuario = :usuario, perfil = :perfil, ativo = :ativo WHERE id = :id";
            $params = [':nome' => $nome, ':usuario' => $usuario, ':perfil' => $perfil, ':ativo' => $ativo, ':id' => $id];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $msg = urlencode("Usuário '{$nome}' atualizado com sucesso!");
        header("Location: usuarios.php?sucesso={$msg}");
        exit;

    } else {
        // CADASTRO NOVO
        if (empty($senha) || strlen($senha) < 4) {
            $msg = urlencode('Para um novo usuário, a senha é obrigatória e deve ter pelo menos 4 caracteres.');
            header("Location: usuario_form.php?erro={$msg}");
            exit;
        }

        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, usuario, senha, perfil, ativo) VALUES (:nome, :usuario, :senha, :perfil, :ativo)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome'    => $nome,
            ':usuario' => $usuario,
            ':senha'   => $hashSenha,
            ':perfil'  => $perfil,
            ':ativo'   => $ativo
        ]);

        $msg = urlencode("Novo usuário '{$nome}' cadastrado com sucesso!");
        header("Location: usuarios.php?sucesso={$msg}");
        exit;
    }

} catch (Throwable $e) {
    $msg = urlencode("Erro ao salvar usuário: " . $e->getMessage());
    header("Location: usuarios.php?erro={$msg}");
    exit;
}
