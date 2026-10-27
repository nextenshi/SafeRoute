<?php
/** SafeRoute — solicitação de código de recuperação. */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions/recuperacao_senha.php';
requireGuest();
$email = '';
$erro = '';
$enviado = false;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    verifyCsrf();
    $email = trim((string)($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $erro = 'Informe um e-mail válido.';
    } else {
        srRequestResetCode($email);
        $_SESSION['reset_email'] = $email;
        $enviado = true;
    }
}
$page_title = 'Esqueci minha senha — SafeRoute';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="auth-main"><div class="container"><div class="auth-wrapper"><section class="card auth-card">
 <div class="auth-header"><span class="auth-icon">🔐</span><h1 class="auth-title">Recuperar acesso</h1><p class="auth-subtitle">Receba um código de 6 dígitos no e-mail cadastrado</p></div>
 <?php if ($erro): ?><div class="alert alert-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
 <?php if ($enviado): ?>
 <div class="alert alert-success" role="status">Se esse e-mail estiver cadastrado e o serviço de envio estiver disponível, você receberá um código. Verifique também o spam.</div>
 <p>O código vale por 10 minutos. Para sua segurança, há limites de envio e tentativas.</p>
 <a href="<?= BASE_URL ?>pages/redefinir_senha.php" class="btn btn-primary btn-block">Digitar código e redefinir senha</a>
 <?php else: ?>
 <form method="post" action="<?= BASE_URL ?>pages/esqueci_senha.php" class="auth-form">
 <?= csrfField() ?>
 <div class="form-group"><label class="form-label" for="email">E-mail da conta</label><input class="form-control" type="email" name="email" id="email" maxlength="150" autocomplete="email" placeholder="seu@email.com" required autofocus value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"></div>
 <button class="btn btn-primary btn-block btn-lg" type="submit">Enviar código por e-mail</button>
 </form>
 <?php endif; ?>
 <div class="auth-footer"><p><a href="<?= BASE_URL ?>pages/login.php">Voltar para o login</a></p></div>
 </section></div></div></main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
