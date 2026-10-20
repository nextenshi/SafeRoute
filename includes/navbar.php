<?php require_once __DIR__.'/auth.php'; require_once __DIR__.'/paginas.php'; $usuarioLogado=currentUser(); $estaLogado=isLoggedIn(); ?>
<nav class="navbar"><div class="container navbar-container">
<a href="<?= BASE_URL ?>" class="navbar-brand"><span class="navbar-logo">🛡️</span><span>SafeRoute</span></a>
<button class="navbar-toggle" id="navbarToggle" aria-label="Abrir menu"><span class="navbar-toggle-icon"></span></button>
<ul class="navbar-nav" id="navbarNav">
<li><a href="<?= BASE_URL ?>" class="nav-link">Início</a></li>
<li><?= linkPagina('mapa.php','Mapa') ?></li><li><?= linkPagina('ocorrencias.php','Ocorrências') ?></li><li><?= linkPagina('rotas.php','Rotas') ?></li><li><?= linkPagina('estatisticas.php','Estatísticas') ?></li>
<?php if($estaLogado): ?><li class="nav-item-user"><span class="nav-user-greeting">👤 <?= htmlspecialchars($usuarioLogado['nome']) ?> <?php if(isAdmin()): ?><span class="badge-admin">ADMIN</span><?php endif; ?></span></li><?php if(isAdmin()): ?><li><a href="<?= BASE_URL ?>pages/admin/dashboard.php" class="nav-link">Painel Admin</a></li><?php else: ?><li><a href="<?= BASE_URL ?>pages/perfil.php" class="nav-link">Meu perfil</a></li><?php endif; ?><li><form action="<?= BASE_URL ?>pages/logout.php" method="post" class="nav-logout-form"><?= csrfField() ?><button type="submit" class="nav-link nav-link-logout nav-logout-button">Sair</button></form></li>
<?php else: ?><li><a href="<?= BASE_URL ?>pages/login.php" class="nav-link">Entrar</a></li><li><a href="<?= BASE_URL ?>pages/cadastro.php" class="btn btn-primary btn-sm">Cadastrar</a></li><?php endif; ?>
</ul></div></nav>
