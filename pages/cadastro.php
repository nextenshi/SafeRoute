<?php
/**
 * SafeRoute — Cadastro de Usuário
 * 
 * Implementa o caso de uso UC01 (Cadastro) e RF01 da SPEC.md.
 * Campos: nome, email, senha.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions/usuario.php';

// Usuários já autenticados não precisam se cadastrar novamente
requireGuest();

$erro = '';
$sucesso = '';
$nome = '';
$email = '';

// Processamento do formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $pdo = getConnection();
    $resultado = cadastrarUsuario($pdo, $nome, $email, $senha);

    if ($resultado['sucesso']) {
        $sucesso = $resultado['mensagem'];
        // Limpar dados do formulário após sucesso
        $nome = '';
        $email = '';
    } else {
        $erro = $resultado['mensagem'];
    }
}

$page_title = 'Cadastro — SafeRoute';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="auth-main">
    <div class="container">
        <div class="auth-wrapper">
            <div class="card auth-card">
                <div class="auth-header">
                    <span class="auth-icon">🛡️</span>
                    <h1 class="auth-title">Criar Conta</h1>
                    <p class="auth-subtitle">Junte-se ao SafeRoute e planeje caminhos mais seguros</p>
                </div>

                <?php if (!empty($erro)): ?>
                    <div class="alert alert-error" role="alert">
                        ⚠️ <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($sucesso)): ?>
                    <div class="alert alert-success" role="alert">
                        ✅ <?= htmlspecialchars($sucesso) ?>
                        <div class="mt-1">
                            <a href="<?= BASE_URL ?>pages/login.php" class="btn btn-primary btn-block">Ir para o Login</a>
                        </div>
                    </div>
                <?php else: ?>
                    <form action="<?= BASE_URL ?>pages/cadastro.php" method="POST" class="auth-form" novalidate>
                        <div class="form-group">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <input 
                                type="text" 
                                id="nome" 
                                name="nome" 
                                class="form-control" 
                                placeholder="Seu nome"
                                value="<?= htmlspecialchars($nome) ?>"
                                required
                                autofocus
                            >
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Endereço de Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control" 
                                placeholder="exemplo@email.com"
                                value="<?= htmlspecialchars($email) ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="senha" class="form-label">Senha</label>
                            <input 
                                type="password" 
                                id="senha" 
                                name="senha" 
                                class="form-control" 
                                placeholder="Mínimo de 6 caracteres"
                                required
                            >
                            <small class="form-text text-muted">Use no mínimo 6 caracteres para sua segurança.</small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg mt-1">
                            Criar Minha Conta
                        </button>
                    </form>
                <?php endif; ?>

                <div class="auth-footer">
                    <p>Já possui uma conta? <a href="<?= BASE_URL ?>pages/login.php">Entrar no sistema</a></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
