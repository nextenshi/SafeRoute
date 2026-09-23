<?php
/**
 * SafeRoute — Autenticação / Login
 * 
 * Implementa o caso de uso UC02 (Login) e RF02 da SPEC.md.
 * Autentica o usuário por email e senha via password_verify().
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions/usuario.php';

// Usuários já autenticados são redirecionados para a página inicial
requireGuest();

$erro = '';
$mensagem = '';
$email = '';

// Mensagem informativa ao sair do sistema
if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    $mensagem = 'Você saiu da sua conta com segurança.';
}

// Processamento do formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        $pdo = getConnection();
        $usuario = buscarUsuarioPorEmail($pdo, $email);

        // Regra de segurança: Não revelar se o email existe ou não.
        // A mesma mensagem genérica é exibida caso o email não seja encontrado ou a senha seja incorreta.
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Regenerar ID da sessão para prevenir Session Fixation
            session_regenerate_id(true);

            // Armazenar apenas dados necessários na sessão (sem a senha)
            $_SESSION['usuario'] = [
                'id_usuario'   => (int) $usuario['id_usuario'],
                'nome'         => $usuario['nome'],
                'email'        => $usuario['email'],
                'tipo_usuario' => $usuario['tipo_usuario'],
            ];

            // Redirecionamento após login bem-sucedido
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        } else {
            $erro = 'Email ou senha incorretos.';
        }
    }
}

$page_title = 'Entrar — SafeRoute';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="auth-main">
    <div class="container">
        <div class="auth-wrapper">
            <div class="card auth-card">
                <div class="auth-header">
                    <span class="auth-icon">🛡️</span>
                    <h1 class="auth-title">Acessar Sistema</h1>
                    <p class="auth-subtitle">Informe suas credenciais para continuar</p>
                </div>

                <?php if (!empty($mensagem)): ?>
                    <div class="alert alert-info" role="alert">
                        ℹ️ <?= htmlspecialchars($mensagem) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($erro)): ?>
                    <div class="alert alert-error" role="alert">
                        ⚠️ <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>pages/login.php" method="POST" class="auth-form" novalidate>
                    <div class="form-group">
                        <label for="email" class="form-label">Endereço de Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control" 
                            placeholder="seu@email.com"
                            value="<?= htmlspecialchars($email) ?>"
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="senha" class="form-label">Senha</label>
                        <input 
                            type="password" 
                            id="senha" 
                            name="senha" 
                            class="form-control" 
                            placeholder="Sua senha"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-1">
                        Entrar no SafeRoute
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Não possui uma conta? <a href="<?= BASE_URL ?>pages/cadastro.php">Cadastre-se gratuitamente</a></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
