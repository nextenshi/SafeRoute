<?php
/** SafeRoute — verificação de código e redefinição da senha. */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions/recuperacao_senha.php';
requireGuest();
$erro = '';
$email = (string)($_SESSION['reset_email'] ?? '');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    verifyCsrf();
    $email = trim((string)($_POST['email'] ?? ''));
    $code = trim((string)($_POST['codigo'] ?? ''));
    $password = (string)($_POST['nova_senha'] ?? '');
    $confirmation = (string)($_POST['confirmar_senha'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150 || !preg_match('/^[0-9]{6}$/D', $code)) {
        $erro = 'Confirme o e-mail e informe um código de 6 dígitos.';
    } elseif (strlen($password) < 10 || strlen($password) > 72) {
        $erro = 'Escolha uma senha de 10 a 72 caracteres.';
    } elseif (!hash_equals($password, $confirmation)) {
        $erro = 'As senhas não coincidem.';
    } elseif (srChangePasswordWithCode($email, $code, $password)) {
        unset($_SESSION['reset_email'], $_SESSION['usuario'], $_SESSION['csrf_token']);
        session_regenerate_id(true);
        header('Location: ' . BASE_URL . 'pages/login.php?redefinida=1', true, 303);
        exit;
    } else {
        $erro = 'Código inválido, expirado ou bloqueado. Solicite um novo código se necessário.';
    }
}
$page_title = 'Redefinir senha — SafeRoute';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="auth-main"><div class="container"><div class="auth-wrapper"><section class="card auth-card">
 <div class="auth-header"><span class="auth-icon">🔑</span><h1 class="auth-title">Redefinir sua senha</h1><p class="auth-subtitle">Informe o código recebido por e-mail e escolha sua nova senha</p></div>
 <?php if ($erro): ?><div class="alert alert-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
 <form method="post" action="<?= BASE_URL ?>pages/redefinir_senha.php" class="auth-form">
 <?= csrfField() ?>
 <div class="form-group"><label class="form-label" for="email">E-mail cadastrado</label><input class="form-control" type="email" id="email" name="email" maxlength="150" autocomplete="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required></div>
 <div class="form-group"><label class="form-label" for="codigo">Código de 6 dígitos</label><input class="form-control" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" minlength="6" id="codigo" name="codigo" placeholder="000000" autocomplete="one-time-code" required></div>
 <div class="form-group"><label class="form-label" for="nova_senha">Nova senha</label><input class="form-control" type="password" id="nova_senha" name="nova_senha" minlength="10" maxlength="72" autocomplete="new-password" required><small class="form-text text-muted">Use de 10 a 72 caracteres.</small></div>
 <div class="form-group"><label class="form-label" for="confirmar_senha">Confirmar nova senha</label><input class="form-control" type="password" id="confirmar_senha" name="confirmar_senha" minlength="10" maxlength="72" autocomplete="new-password" required></div>
 <button class="btn btn-primary btn-block btn-lg" type="submit">Salvar minha nova senha</button>
 </form>
 <div class="auth-footer"><p>Não recebeu? <a href="<?= BASE_URL ?>pages/esqueci_senha.php">Solicitar outro código</a></p><p><a href="<?= BASE_URL ?>pages/login.php">Voltar ao login</a></p></div>
 </section></div></div></main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
