<?php
/**
 * SafeRoute — Rodapé HTML
 * 
 * Incluído no final de todas as páginas.
 * 
 * Variável opcional (definir ANTES de incluir este arquivo):
 *   $extra_js : array — URLs de scripts JS adicionais (ex: Leaflet, mapa.js)
 */

$extra_js = $extra_js ?? [];
?>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <!-- Sobre -->
            <div class="footer-section">
                <h3>🛡️ SafeRoute</h3>
                <p>Segurança inteligente para seus caminhos.</p>
                <p class="footer-disclaimer">
                    Este é um projeto escolar com fins educacionais.
                </p>
            </div>

            <!-- Navegação -->
            <div class="footer-section">
                <h4>Navegação</h4>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>">Início</a></li>
                    <li><a href="<?= BASE_URL ?>pages/mapa.php">Mapa</a></li>
                    <li><a href="<?= BASE_URL ?>pages/ocorrencias.php">Ocorrências</a></li>
                    <li><a href="<?= BASE_URL ?>pages/rotas.php">Rotas</a></li>
                </ul>
            </div>

            <!-- Informações -->
            <div class="footer-section">
                <h4>Informações</h4>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>pages/estatisticas.php">Estatísticas</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> SafeRoute — Projeto Escolar</p>
        </div>
    </div>
</footer>

<!-- JavaScript principal -->
<script src="<?= BASE_URL ?>assets/js/main.js"></script>

<!-- JavaScript adicional (definido por cada página) -->
<?php foreach ($extra_js as $js): ?>
<script src="<?= htmlspecialchars($js) ?>"></script>
<?php endforeach; ?>

</body>
</html>
