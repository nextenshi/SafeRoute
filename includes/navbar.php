<?php
/**
 * SafeRoute — Barra de Navegação
 * 
 * Navbar responsiva com menu mobile (toggle via JS)
 * e controle de exibição de acordo com o status de autenticação do usuário.
 */

require_once __DIR__ . '/auth.php';

$usuarioLogado = currentUser();
$estaLogado = isLoggedIn();
?>

<!-- Navbar -->
<nav class="navbar">
    <div class="container navbar-container">
        <!-- Logo e nome -->
        <a href="<?= BASE_URL ?>" class="navbar-brand">
            <span class="navbar-logo">🛡️</span>
            <span>SafeRoute</span>
        </a>

        <!-- Botão toggle mobile -->
        <button class="navbar-toggle" id="navbarToggle" aria-label="Abrir menu de navegação">
            <span class="navbar-toggle-icon"></span>
        </button>

        <!-- Links de navegação -->
        <ul class="navbar-nav" id="navbarNav">
            <li><a href="<?= BASE_URL ?>" class="nav-link">Início</a></li>
            <li><a href="<?= BASE_URL ?>pages/mapa.php" class="nav-link">Mapa</a></li>
            <li><a href="<?= BASE_URL ?>pages/ocorrencias.php" class="nav-link">Ocorrências</a></li>
            <li><a href="<?= BASE_URL ?>pages/rotas.php" class="nav-link">Rotas</a></li>
            <li><a href="<?= BASE_URL ?>pages/estatisticas.php" class="nav-link">Estatísticas</a></li>

            <?php if ($estaLogado): ?>
                <li class="nav-item-user">
                    <span class="nav-user-greeting">
                        👤 <?= htmlspecialchars($usuarioLogado['nome']) ?>
                        <?php if (isAdmin()): ?>
                            <span class="badge-admin">Admin</span>
                        <?php endif; ?>
                    </span>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>pages/logout.php" class="nav-link nav-link-logout">
                        Sair
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a href="<?= BASE_URL ?>pages/login.php" class="nav-link">
                        Entrar
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>pages/cadastro.php" class="btn btn-primary btn-sm">
                        Cadastrar
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
