<?php
// ====================================================================
// LOGOUT (SAIR DO PAINEL)
// ====================================================================
// Limpa a sessao do navegador e desloga o usuario por seguranca.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpa todas as informacoes guardadas na sessao
$_SESSION = [];

// Destroi a sessao
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Manda de volta pra tela de login
header('Location: login.php');
exit;
