<?php
/**
 * SafeRoute — Barra de Navegação
 * 
 * Navbar responsiva com menu mobile (toggle via JS).
 * Links de autenticação serão adicionados na Etapa 2.
 */
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
        </ul>
    </div>
</nav>
