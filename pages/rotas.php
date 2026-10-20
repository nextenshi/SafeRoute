<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = getConnection();
$resultado = null;
$ocorrenciasDaAnalise = [];
$erro = '';
$valores = [
    'origem' => trim((string)($_POST['origem'] ?? '')),
    'destino' => trim((string)($_POST['destino'] ?? '')),
    'olat' => (string)($_POST['olat'] ?? ''),
    'olng' => (string)($_POST['olng'] ?? ''),
    'dlat' => (string)($_POST['dlat'] ?? ''),
    'dlng' => (string)($_POST['dlng'] ?? ''),
    'transporte' => (string)($_POST['transporte'] ?? 'carro'),
];

if (!in_array($valores['transporte'], ['carro', 'moto', 'pe', 'bicicleta', 'onibus'], true)) {
    $valores['transporte'] = 'carro';
}

function distanciaKm(float $a, float $b, float $c, float $d): float
{
    $r = 6371;
    $p1 = deg2rad($a);
    $p2 = deg2rad($c);
    $dp = deg2rad($c - $a);
    $dl = deg2rad($d - $b);
    $x = sin($dp / 2) ** 2 + cos($p1) * cos($p2) * sin($dl / 2) ** 2;
    return 2 * $r * asin(min(1, sqrt($x)));
}

function coordenadaValida(string $valor, float $min, float $max): bool
{
    return $valor !== '' && is_numeric($valor) && is_finite((float)$valor)
        && (float)$valor >= $min && (float)$valor <= $max;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 verifyCsrf();
    requireLogin();
    if (!$valores['origem'] || !$valores['destino'] || mb_strlen($valores['origem']) > 255 || mb_strlen($valores['destino']) > 255
        || !coordenadaValida($valores['olat'], -90, 90)
        || !coordenadaValida($valores['dlat'], -90, 90)
        || !coordenadaValida($valores['olng'], -180, 180)
        || !coordenadaValida($valores['dlng'], -180, 180)) {
        $erro = 'Escolha uma origem e um destino no mapa ou use o botão Buscar no mapa para encontrar cada lugar.';
    } else {
        $olat = (float)$valores['olat'];
        $olng = (float)$valores['olng'];
        $dlat = (float)$valores['dlat'];
        $dlng = (float)$valores['dlng'];
        $dist = distanciaKm($olat, $olng, $dlat, $dlng);

        // Indicador simplificado para fins escolares; não calcula uma rota viária.
        $delta = max(0.01, min(0.08, $dist / 100));
        // A mesma seleção que alimenta o indicador também aparece no mapa.
        // Apenas registros aprovados, preservando a regra original de localização.
        $st = $pdo->prepare("SELECT o.id_ocorrencia, o.latitude, o.longitude,
            o.descricao, o.data, o.horario, t.nome_tipo
            FROM ocorrencia o JOIN tipo_ocorrencia t ON t.id_tipo = o.id_tipo
            WHERE o.status='aprovada'
            AND o.latitude BETWEEN ? AND ? AND o.longitude BETWEEN ? AND ?
            ORDER BY o.data DESC, o.id_ocorrencia DESC");
        $st->execute([
            min($olat, $dlat) - $delta, max($olat, $dlat) + $delta,
            min($olng, $dlng) - $delta, max($olng, $dlng) + $delta,
        ]);
        $ocorrenciasDaAnalise = $st->fetchAll(PDO::FETCH_ASSOC);
        $n = count($ocorrenciasDaAnalise);
        $risco = min(100, round($n * 12 + ($dist > 10 ? 10 : 0)));
        $nivel = $risco <= 25 ? 'Baixo' : ($risco <= 50 ? 'Moderado' : ($risco <= 75 ? 'Alto' : 'Muito alto'));
        $resultado = compact('dist', 'n', 'risco', 'nivel', 'olat', 'olng', 'dlat', 'dlng');
        $resultado['o'] = $valores['origem'];
        $resultado['d'] = $valores['destino'];

        $st = $pdo->prepare('INSERT INTO rota(id_usuario,origem,destino,distancia,risco_estimado) VALUES(?,?,?,?,?)');
        $st->execute([currentUser()['id_usuario'], $valores['origem'], $valores['destino'], $dist, $risco]);
    }
}

$page_title = 'SafeRoute — Planejar rota';
$extra_css = ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'];
$extra_js = ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="container page-main">
    <section class="page-hero">
        <span class="eyebrow">ROTAS</span>
        <h1>Planejar uma rota</h1>
        <p>Escolha de onde você sai e para onde quer ir. Não é necessário saber coordenadas.</p>
    </section>

    <?php if ($erro): ?>
        <div class="alert alert-error" role="alert">⚠️ <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!isLoggedIn()): ?>
        <div class="card route-login-note">
            <p>Você pode consultar os lugares no mapa. Para analisar e salvar a consulta, <a href="<?= BASE_URL ?>pages/login.php">entre na sua conta</a>.</p>
        </div>
    <?php endif; ?>

    <section class="card route-planner-card">
        <h2>De onde você vai sair?</h2>
        <p class="muted">Digite o nome de um lugar, rua ou endereço e clique em <strong>Buscar no mapa</strong>. Você também pode tocar diretamente no mapa.</p>
        <form method="post" id="routePlannerForm" class="route-planner-form"><?= csrfField() ?>
            <div class="route-field">
                <label for="routeOrigin" class="form-label">Origem</label>
                <div class="route-search-row">
                    <input class="form-control" id="routeOrigin" name="origem" placeholder="Ex.: IFRO Campus Porto Velho Calama" autocomplete="street-address" value="<?= htmlspecialchars($valores['origem'], ENT_QUOTES, 'UTF-8') ?>" required>
                    <button type="button" class="btn btn-secondary route-search-btn" data-search="origin">Buscar no mapa</button>
                </div>
                <span class="route-choice-status" id="originStatus">Origem ainda não selecionada.</span>
            </div>
            <div class="route-field">
                <label for="routeDestination" class="form-label">Destino</label>
                <div class="route-search-row">
                    <input class="form-control" id="routeDestination" name="destino" placeholder="Ex.: Praça das Três Caixas D'Água" autocomplete="off" value="<?= htmlspecialchars($valores['destino'], ENT_QUOTES, 'UTF-8') ?>" required>
                    <button type="button" class="btn btn-secondary route-search-btn" data-search="destination">Buscar no mapa</button>
                </div>
                <span class="route-choice-status" id="destinationStatus">Destino ainda não selecionado.</span>
            </div>
            <fieldset class="route-transport-picker" aria-label="Meio de transporte">
                <legend>Como você pretende ir?</legend>
                <div class="route-transport-options" id="routeTransportOptions">
                    <button type="button" class="route-transport-button" data-transport="carro" aria-pressed="false">🚗 <span>Carro</span></button>
                    <button type="button" class="route-transport-button" data-transport="moto" aria-pressed="false">🏍️ <span>Moto</span></button>
                    <button type="button" class="route-transport-button" data-transport="pe" aria-pressed="false">🚶 <span>A pé</span></button>
                    <button type="button" class="route-transport-button" data-transport="bicicleta" aria-pressed="false">🚲 <span>Bicicleta</span></button>
                    <button type="button" class="route-transport-button" data-transport="onibus" aria-pressed="false">🚌 <span>Ônibus</span></button>
                </div>
                <input type="hidden" name="transporte" id="routeTransport" value="<?= htmlspecialchars($valores['transporte'], ENT_QUOTES, 'UTF-8') ?>">
            </fieldset>
            <div class="route-map-tools" role="group" aria-label="Selecionar pontos no mapa">
                <button type="button" class="btn btn-secondary route-mark-btn is-selected" data-pick="origin" aria-pressed="true">📍 Marcar origem</button>
                <button type="button" class="btn btn-secondary route-mark-btn" data-pick="destination" aria-pressed="false">🏁 Marcar destino</button>
                <button type="button" class="btn btn-secondary" id="useMyLocation">⌖ Usar minha localização como origem</button>
            </div>
            <p id="routeHelp" class="route-map-help" role="status" aria-live="polite">Clique no mapa para marcar a origem. Depois, marque o destino.</p>
            <div id="routePickerMap" class="safe-map route-picker-map" role="region" aria-label="Mapa interativo para escolher origem e destino"></div>
            <div class="route-map-legend" aria-label="Legenda do mapa">
                <span><i class="route-legend-dot" style="background:#0284c7"></i> Caminho pelas ruas</span>
                <span><i class="route-legend-dot" style="background:#e11d48"></i> Ocorrências aprovadas da análise</span>
                <span><i class="route-legend-dot" style="background:#facc15"></i> Paradas de ônibus disponíveis no OSM</span>
                <span><i class="route-legend-dot" style="background:#7c3aed"></i> Linha de ônibus selecionada</span>
            </div>
            <section class="route-stops-toolbar" aria-label="Consulta de ônibus no mapa">
                <h3>🚌 Paradas de ônibus no mapa</h3>
                <p class="muted">A busca de paradas agora funciona em qualquer modo de transporte. Os pontos amarelos vêm do OpenStreetMap e podem ser diferentes dos exibidos pelo Google Maps.</p>
                <div class="route-bus-actions">
                    <button type="button" class="btn btn-secondary" id="loadBusData">↻ Buscar paradas nesta região</button>
                    <label class="route-toggle"><input type="checkbox" id="showBusStops" checked> Mostrar paradas no mapa</label>
                    <a class="btn btn-secondary" id="viewStopsOnGoogle" href="https://www.google.com/maps/search/?api=1&amp;query=paradas%20de%20onibus%20Porto%20Velho" target="_blank" rel="noopener noreferrer">↗ Conferir no Google Maps</a>
                    <a class="btn btn-secondary" href="https://www.portovelho.ro.gov.br/horarios-dos-onibus" target="_blank" rel="noopener noreferrer">↗ Linhas e horários oficiais</a>
                </div>
                <p class="route-bus-status" id="routeBusStatus" role="status" aria-live="polite">Procurando paradas próximas no OpenStreetMap...</p>
            </section>
            <?php if ($resultado): ?>
            <label class="route-toggle"><input type="checkbox" id="showRiskMarkers" checked> Mostrar <?= count($ocorrenciasDaAnalise) ?> ocorrência(s) incluída(s) no indicador no mapa</label>
            <?php endif; ?>
            <p class="route-attribution-info">Busca de lugares: <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">© OpenStreetMap</a> e colaboradores. Requer internet.</p>

            <!-- Coordenadas preenchidas automaticamente pelos botões de busca ou pelo mapa. -->
            <input type="hidden" name="olat" id="originLat" value="<?= htmlspecialchars($valores['olat'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="olng" id="originLng" value="<?= htmlspecialchars($valores['olng'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="dlat" id="destinationLat" value="<?= htmlspecialchars($valores['dlat'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="dlng" id="destinationLng" value="<?= htmlspecialchars($valores['dlng'], ENT_QUOTES, 'UTF-8') ?>">
            <section class="route-live-result" id="routeLiveResult" aria-label="Trajeto pelas ruas">
                <div class="route-live-heading">
                    <div>
                        <span class="eyebrow">TRAJETO PELAS RUAS</span>
                        <h3>Como chegar ao destino</h3>
                    </div>
                    <span class="route-mode-badge" id="routeModeBadge">🚗 De carro</span>
                </div>
                <p class="route-live-status" id="routeRoadStatus" role="status" aria-live="polite">Selecione a origem e o destino para calcular o caminho pelas ruas.</p>
                <div id="routeRoadSummary" class="route-road-summary" hidden>
                    <div><strong id="roadDistance">—</strong><span>Distância pela rota</span></div>
                    <div><strong id="roadDuration">—</strong><span>Tempo estimado</span></div>
                </div>
                <div class="route-direction-panel" id="routeDirectionsPanel" hidden>
                    <h4>Direções passo a passo</h4>
                    <ol id="routeDirectionsList" class="route-directions-list"></ol>
                </div>
                <a id="openGoogleMaps" class="btn btn-secondary route-open-navigation" href="#" target="_blank" rel="noopener noreferrer" hidden>↗ Abrir navegação no Google Maps</a>
                <p class="route-routing-note" id="routeRoutingNote">Distância e duração aproximadas, sem trânsito em tempo real. Confirme as condições locais; não use como navegação ao vivo.</p>
                <section class="route-bus-section" id="routeBusSection" hidden aria-label="Ônibus, linhas e paradas">
                    <h4>🚌 Linhas e paradas de ônibus</h4>
                    <p>Selecione uma parada amarela para ver as linhas vinculadas nos dados colaborativos. Para visualizar o traçado roxo de uma linha, clique nela na lista. Se o OSM não trouxer dados, consulte os links oficiais acima.</p>
                    <div class="route-bus-grid">
                        <section><h5>Paradas cadastradas no mapa</h5><ul id="routeBusStops" class="route-bus-list"></ul></section>
                        <section><h5>Linhas mapeadas na região</h5><ul id="routeBusLines" class="route-bus-list"></ul></section>
                    </div>
                    <p class="route-bus-disclaimer">Paradas e linhas são mapeadas por voluntários no OpenStreetMap e podem faltar ou estar desatualizadas. Uma linha próxima não significa que ela para naquele ponto: só é identificada como vinculada quando a relação do OSM inclui a parada. Para horários e trajetos oficiais de Porto Velho, <a href="https://pvhmais.portovelho.ro.gov.br/" target="_blank" rel="noopener noreferrer">consulte o PVH+ da Prefeitura</a>. Sem dados oficiais (GTFS), o SafeRoute não calcula baldeações nem previsão de chegada.</p>
                </section>
            </section>
            <button type="submit" class="btn btn-primary btn-lg" id="analyzeRoute">Analisar e salvar consulta</button>
        </form>
        <p class="route-safety-note"><strong>Importante:</strong> a rota pelas ruas é calculada por um serviço externo. O indicador de risco escolar continua baseado apenas nos registros cadastrados entre os pontos e <strong>não garante segurança</strong> nem avalia cada rua.</p>
    </section>

    <?php if ($resultado): ?>
        <section class="route-result" aria-label="Resultado da análise">
            <div>
                <span class="eyebrow">RESULTADO</span>
                <h2><?= htmlspecialchars($resultado['o'], ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars($resultado['d'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p>Distância em linha reta (referência para o indicador escolar): <strong><?= number_format($resultado['dist'], 2, ',', '.') ?> km</strong></p>
                <p>Ocorrências consideradas na região entre os pontos: <strong><?= $resultado['n'] ?></strong></p>
                <p class="route-risk-map-hint">📍 Veja no mapa os pontos vermelhos das ocorrências aprovadas usadas nesta análise. Clique em cada ponto para ver tipo e data.</p>
                <p>Indicador de risco estimado: <strong><?= $resultado['nivel'] ?></strong> (<?= $resultado['risco'] ?>/100)</p>
                <small>Indicador experimental de registros na região, independente do caminho pelas ruas. Não representa avaliação de segurança viária.</small>
            </div>
            <div id="routeMap" class="safe-map"></div>
        </section>
    <?php endif; ?>
</main>
<?php if ($resultado): ?>
<script type="application/json" id="routeRiskData"><?= json_encode($ocorrenciasDaAnalise, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<?php endif; ?>
<script src="<?= BASE_URL ?>assets/js/rotas.js?v=5" defer></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
