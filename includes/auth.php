<?php
/**
 * SafeRoute — Autenticação e Controle de Sessão
 * 
 * Responsável por gerenciar sessões, verificação de login
 * e controle de acesso para usuários comuns e administradores.
 */

// Iniciar sessão com parâmetros seguros se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Configurações recomendadas para segurança de sessão
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    // Se estiver em PHP 7.3+, configura samesite e httponly
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/**
 * Verifica se há um usuário autenticado na sessão atual.
 *
 * @return bool True se estiver logado, false caso contrário
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['usuario']) && is_array($_SESSION['usuario']) && !empty($_SESSION['usuario']['id_usuario']);
}

/**
 * Retorna os dados do usuário atualmente autenticado.
 *
 * @return array|null Dados do usuário na sessão ou null se não autenticado
 */
function currentUser(): ?array
{
    return isLoggedIn() ? $_SESSION['usuario'] : null;
}

/**
 * Verifica se o usuário autenticado possui perfil de administrador.
 * Conforme a SPEC.md (Seção 4.2 e Seção 8 - tipo_usuario: 'admin').
 *
 * @return bool True se for admin, false caso contrário
 */
function isAdmin(): bool
{
    return isLoggedIn() && (($_SESSION['usuario']['tipo_usuario'] ?? '') === 'admin');
}

/**
 * Exige que o usuário esteja autenticado para acessar a página.
 * Se não estiver autenticado, redireciona para a página de login.
 *
 * @return void
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        $loginUrl = defined('BASE_URL') ? BASE_URL . 'pages/login.php' : '/SafeRoute/pages/login.php';
        header('Location: ' . $loginUrl);
        exit;
    }
}

/**
 * Exige que o usuário seja um convidado (NÃO autenticado).
 * Usado em páginas como login e cadastro para evitar que usuários já logados acessem.
 * Redireciona para a página inicial se já estiver autenticado.
 *
 * @return void
 */
function requireGuest(): void
{
    if (isLoggedIn()) {
        $homeUrl = defined('BASE_URL') ? BASE_URL : '/SafeRoute/';
        header('Location: ' . $homeUrl);
        exit;
    }
}

/**
 * Exige que o usuário seja administrador.
 * Se não for, bloqueia o acesso com HTTP 403 Forbidden.
 *
 * @return void
 */
function requireAdmin(): void
{
    requireLogin();

    if (!isAdmin()) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Acesso Negado</title></head><body>';
        echo '<h1>403 - Acesso Negado</h1>';
        echo '<p>Você não tem permissão para acessar esta área.</p>';
        echo '<p><a href="' . (defined('BASE_URL') ? BASE_URL : '/SafeRoute/') . '">Voltar para o início</a></p>';
        echo '</body></html>';
        exit;
    }
}
