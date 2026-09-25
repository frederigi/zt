<?php
// ====================================================================
// CONTROLE DE SESSÃO E PERMISSÕES DO PAINEL ADMIN
// ====================================================================
// Esse arquivo cuida da seguranca do nosso painel.
// Ele verifica se a pessoa digitou a senha certa antes de deixar ver as telas.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Puxa a conexao com o banco de dados
require_once __DIR__ . '/../../config/conexao.php';

/**
 * Funcao de seguranca que garante que a tabela de usuarios existe no MySQL.
 * Se ainda nao tiver sido criada no banco, a gente cria agora com os usuarios padrao.
 */
function garantirTabelaUsuarios(PDO $pdo): void
{
    try {
        $sql = "CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            usuario VARCHAR(50) NOT NULL UNIQUE,
            senha VARCHAR(255) NOT NULL,
            perfil ENUM('artesa', 'superadmin') NOT NULL DEFAULT 'artesa',
            ativo TINYINT(1) NOT NULL DEFAULT 1,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;";
        $pdo->exec($sql);

        // Confere se ja tem o superadmin cadastrado
        $check = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE usuario = 'superadmin'")->fetchColumn();
        if ((int)$check === 0) {
            // Cria o superadmin (senha: univesp) e a artesa (senha: zebra123)
            $hashSuper = password_hash('univesp', PASSWORD_DEFAULT);
            $hashArtesa = password_hash('zebra123', PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, usuario, senha, perfil, ativo) VALUES 
                ('Administrador do Sistema', 'superadmin', :senhaSuper, 'superadmin', 1),
                ('Artesã Zebra de Touca', 'artesa', :senhaArtesa, 'artesa', 1)");
            $stmt->execute([':senhaSuper' => $hashSuper, ':senhaArtesa' => $hashArtesa]);
        }
    } catch (Throwable $e) {
        // Se der algum erro aqui, deixa seguir que o try/catch das telas trata
    }
}

garantirTabelaUsuarios($pdo);

/**
 * Bloqueia o acesso a qualquer pagina se a pessoa nao estiver logada.
 */
function exigirLogin(): void
{
    if (empty($_SESSION['usuario_logado'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Devolve os dados da pessoa que esta logada no momento.
 */
function usuarioLogado(): ?array
{
    return $_SESSION['usuario_logado'] ?? null;
}

/**
 * Verifica se quem esta usando o sistema e o superadmin.
 */
function isSuperAdmin(): bool
{
    $usuario = usuarioLogado();
    return ($usuario && ($usuario['perfil'] ?? '') === 'superadmin');
}

/**
 * Trava a tela se a pessoa tentar entrar numa area exclusiva do superadmin
 * (como a gestao de usuarios).
 */
function exigirSuperAdmin(): void
{
    exigirLogin();
    if (!isSuperAdmin()) {
        die("Acesso negado: essa area e permitida apenas para o perfil de Administrador do Sistema.");
    }
}
