<?php require_once __DIR__.'/paginas.php'; ?>
<footer class="footer"><div class="container footer-content">
<div class="footer-section"><h3>🛡️ SafeRoute</h3><p>Segurança inteligente para seus caminhos.</p><p class="footer-disclaimer">Projeto escolar com fins educacionais. Dados demonstrativos devem ser identificados.</p></div>
<div class="footer-section"><h4>Navegação</h4><ul class="footer-links"><li><a href="<?= BASE_URL ?>">Início</a></li><li><?= linkPagina('mapa.php','Mapa','','footer-soon') ?></li><li><?= linkPagina('ocorrencias.php','Ocorrências','','footer-soon') ?></li><li><?= linkPagina('rotas.php','Rotas','','footer-soon') ?></li></ul></div>
<div class="footer-section"><h4>Conta</h4><ul class="footer-links"><li><?= isLoggedIn()?'<a href="'.BASE_URL.'pages/perfil.php">Meu perfil</a>':'<a href="'.BASE_URL.'pages/login.php">Entrar</a>' ?></li><li><a href="<?= BASE_URL ?>pages/estatisticas.php">Estatísticas</a></li></ul></div>
</div><div class="container footer-bottom"><p>&copy; <?= date('Y') ?> SafeRoute — Projeto Escolar</p></div></footer>
<script src="<?= BASE_URL ?>assets/js/main.js"></script></body></html>
