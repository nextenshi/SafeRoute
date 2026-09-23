<?php
/**
 * SafeRoute — Logout de Usuário
 * 
 * Encerra a sessão do usuário de forma segura,
 * invalidando o cookie de sessão e destruindo os dados em memória.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Limpar todos os dados da superglobal $_SESSION
$_SESSION = [];

// Se cookies de sessão forem usados, remove o cookie no cliente
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destruir a sessão no servidor
session_destroy();

// Redirecionar para a tela de login com indicação de logout
header('Location: ' . BASE_URL . 'pages/login.php?logout=1');
exit;
