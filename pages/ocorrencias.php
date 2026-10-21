<?php
/**
 * SafeRoute — Registro de ocorrências com localização acessível.
 * Usuário pesquisa um endereço, escolhe no mapa ou usa GPS com permissão.
 * Coordenadas ficam ocultas e são validadas no servidor.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = getConnection();
$erro = '';
$ok = '';
$tipos = $pdo->query('SELECT id_tipo, nome_tipo FROM tipo_ocorrencia ORDER BY nome_tipo')->fetchAll();
$valores = [
    'id_tipo' => (int)($_POST['id_tipo'] ?? 0),
    'data' => (string)($_POST['data'] ?? date('Y-m-d')),
    'horario' => (string)($_POST['horario'] ?? date('H:i')),
    'descricao' => (string)($_POST['descricao'] ?? ''),
    'latitude' => (string)($_POST['latitude'] ?? ''),
    'longitude' => (string)($_POST['longitude'] ?? ''),
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    verifyCsrf();
    $descricao = trim($valores['descricao']);
    $latOk = $valores['latitude'] !== '' && is_numeric($valores['latitude'])
        && is_finite((float)$valores['latitude']) && (float)$valores['latitude'] >= -90
        && (float)$valores['latitude'] <= 90;
    $lngOk = $valores['longitude'] !== '' && is_numeric($valores['longitude'])
        && is_finite((float)$valores['longitude']) && (float)$valores['longitude'] >= -180
        && (float)$valores['longitude'] <= 180;
    $dateObject = DateTimeImmutable::createFromFormat('!Y-m-d', $valores['data']);
    $dataOk = $dateObject && $dateObject->format('Y-m-d') === $valores['data'];
    $horaOk = preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $valores['horario']) === 1;
    $tipoOk = in_array($valores['id_tipo'], array_map(
        static fn($t) => (int)$t['id_tipo'], $tipos
    ), true);

    if (!$tipoOk || !$dataOk || !$horaOk) {
        $erro = 'Escolha um tipo de ocorrência, uma data e um horário válidos.';
    } elseif (!$latOk || !$lngOk) {
        $erro = 'Escolha o local da ocorrência pesquisando um endereço ou tocando no mapa.';
    } elseif (mb_strlen($descricao) > 1000) {
        $erro = 'A descrição deve ter no máximo 1000 caracteres.';
    } else {
        $st = $pdo->prepare(
            'INSERT INTO ocorrencia(id_usuario,id_tipo,descricao,latitude,longitude,data,horario,status,fonte)
             VALUES(?,?,?,?,?,?,?,"pendente","usuario")'
        );
        $st->execute([
            currentUser()['id_usuario'], $valores['id_tipo'], $descricao,
            (float)$valores['latitude'], (float)$valores['longitude'],
            $valores['data'], $valores['horario'],
        ]);
        $ok = 'Ocorrência enviada. Ela ficará pendente até análise administrativa.';
        $valores['latitude'] = '';
        $valores['longitude'] = '';
        $valores['descricao'] = '';
    }
}
$recentes = $pdo->query(
    "SELECT o.id_ocorrencia,o.descricao,o.latitude,o.longitude,o.data,o.horario,o.status,t.nome_tipo
     FROM ocorrencia o JOIN tipo_ocorrencia t ON t.id_tipo=o.id_tipo
     WHERE o.status='aprovada' ORDER BY o.data DESC,o.horario DESC LIMIT 20"
)->fetchAll();

$page_title = 'SafeRoute — Ocorrências';
$extra_css = ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'];
$extra_js = ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<style>
/* Interface específica de seleção de localização, sem modificar estilos globais. */
.occ-location{grid-column:1/-1;border:1px solid #dbeafe;background:#f8fbff;border-radius:14px;padding:16px;min-width:0}
.occ-location h3{color:#0f172a;font-size:17px;margin:0 0 4px}
.occ-location p{color:#475569;font-size:13px;margin:0 0 13px;line-height:1.55}
.occ-search-row{display:flex;gap:9px;align-items:stretch}
.occ-search-row input{min-width:0;flex:1}
.occ-search-row button{flex-shrink:0}
.occ-location-tools{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
.occ-location-status{margin:13px 0 0!important;padding:11px 13px;border-radius:9px;background:#e2e8f0;color:#334155!important;min-height:42px}
.occ-location-status.is-ready{background:#dcfce7;color:#166534!important}
.occ-results{list-style:none!important;padding:0!important;margin:10px 0 0!important;background:white;border:1px solid #cbd5e1;border-radius:10px;max-height:260px;overflow-y:auto}
.occ-results[hidden]{display:none}
.occ-results li+li{border-top:1px solid #e2e8f0}
.occ-result-button{display:block;width:100%;border:0;padding:12px;text-align:left;cursor:pointer;background:transparent;color:#0f172a;font:inherit;font-size:13px;line-height:1.5}
.occ-result-button:hover,.occ-result-button:focus-visible{background:#eff6ff}
.occ-map-help{font-size:13px;color:#475569;margin:0 0 10px;line-height:1.5}
.occ-map-selection{font-size:13px;padding:10px 12px;border-radius:9px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;margin-top:11px}
.occ-map-legend{display:flex;gap:15px;flex-wrap:wrap;margin-top:8px;font-size:12px;color:#475569}
.occ-dot{display:inline-block;width:11px;height:11px;border-radius:50%;margin-right:5px;vertical-align:-1px}
.occ-privacy{font-size:12px!important;line-height:1.55!important;color:#64748b!important;margin:12px 0 0!important}
.occ-form-actions{display:flex;flex-wrap:wrap;gap:9px;align-items:center}
@media(max-width:640px){.occ-search-row{flex-direction:column}.occ-search-row button{width:100%}.occ-location{padding:12px}.occ-form-actions button{flex:1}}
</style>
<main class="container page-main">
    <section class="page-hero">
        <span class="eyebrow">OCORRÊNCIAS</span>
        <h1>Registros de segurança</h1>
        <p>Consulte ocorrências aprovadas e, se estiver conectado, envie um novo relato.</p>
    </section>

    <?php if ($erro): ?><div class="alert alert-error" role="alert">⚠️ <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($ok): ?><div class="alert alert-success" role="status">✅ <?= htmlspecialchars($ok, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <div class="content-grid">
        <section class="card form-card">
            <h2>Registrar ocorrência</h2>
            <?php if (!isLoggedIn()): ?>
                <p>Faça login para registrar uma ocorrência.</p>
                <a class="btn btn-primary" href="<?= BASE_URL ?>pages/login.php">Entrar</a>
            <?php else: ?>
                <p class="muted">Seu registro será enviado como pendente para análise.</p>
                <form id="occurrenceForm" method="post" class="form-grid">
                    <?= csrfField() ?>
                    <div class="form-group">
                        <label class="form-label" for="occType">Tipo</label>
                        <select id="occType" name="id_tipo" class="form-control" required>
                            <option value="">Selecione</option>
                            <?php foreach($tipos as $t): ?>
                            <option value="<?= (int)$t['id_tipo'] ?>" <?= (int)$t['id_tipo'] === $valores['id_tipo'] ? 'selected' : '' ?>><?= htmlspecialchars($t['nome_tipo'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="occDate">Data</label>
                        <input id="occDate" type="date" name="data" class="form-control" value="<?= htmlspecialchars($valores['data'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="occTime">Horário</label>
                        <input id="occTime" type="time" name="horario" class="form-control" value="<?= htmlspecialchars($valores['horario'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="occ-location">
                        <h3>📍 Onde aconteceu?</h3>
                        <p>Digite um endereço ou nome de lugar, selecione o resultado e confira o ponto no mapa ao lado. Também pode clicar no mapa.</p>
                        <label for="occAddress" class="form-label">Buscar rua ou lugar</label>
                        <div class="occ-search-row">
                            <input id="occAddress" type="search" class="form-control" placeholder="Ex.: Avenida Calama, Porto Velho" autocomplete="street-address">
                            <button class="btn btn-secondary" type="button" id="occSearch">🔎 Buscar no mapa</button>
                        </div>
                        <ul id="occResults" class="occ-results" aria-label="Endereços encontrados" hidden></ul>
                        <div class="occ-location-tools">
                            <button type="button" class="btn btn-secondary" id="useLocation">📍 Usar minha localização</button>
                            <button type="button" class="btn btn-secondary" id="occClear">Limpar local</button>
                        </div>
                        <p id="occStatus" class="occ-location-status" role="status" aria-live="polite">Nenhum local selecionado. Pesquise um lugar ou clique no mapa.</p>
                        <p class="occ-privacy">O ponto de uma ocorrência aprovada poderá ser visto publicamente. Evite indicar endereços privados ou dados pessoais desnecessários.</p>
                        <input id="lat" type="hidden" name="latitude" value="<?= htmlspecialchars($valores['latitude'], ENT_QUOTES, 'UTF-8') ?>">
                        <input id="lng" type="hidden" name="longitude" value="<?= htmlspecialchars($valores['longitude'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group full">
                        <label class="form-label" for="occDescription">Descrição</label>
                        <textarea id="occDescription" name="descricao" class="form-control" rows="4" maxlength="1000" placeholder="Descreva o ocorrido..."><?= htmlspecialchars($valores['descricao'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="full occ-form-actions">
                        <button type="submit" class="btn btn-primary">Enviar ocorrência</button>
                    </div>
                </form>
            <?php endif; ?>
        </section>
        <section aria-label="Mapa de localização das ocorrências">
            <p class="occ-map-help"><strong>🗺️ Clique no mapa para marcar onde aconteceu.</strong> O marcador azul é o ponto escolhido; os círculos vermelhos são ocorrências aprovadas.</p>
            <div id="safeMap" class="safe-map" role="application" aria-label="Mapa interativo: clique para escolher o local"></div>
            <p class="occ-map-selection" id="occMapStatus" role="status" aria-live="polite">Aguardando seleção no mapa.</p>
            <div class="occ-map-legend">
                <span><i class="occ-dot" style="background:#2563eb"></i>Local selecionado</span>
                <span><i class="occ-dot" style="background:#e11d48"></i>Ocorrências aprovadas</span>
            </div>
            <p class="map-info"><strong><?= count($recentes) ?></strong> ocorrência(s) aprovada(s) exibida(s). <span class="muted">O indicador de risco é experimental e não representa uma avaliação de segurança real.</span></p>
        </section>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
    'use strict';
    const mapElement = document.getElementById('safeMap');
    if (!mapElement) return;
    const form = document.getElementById('occurrenceForm');
    const status = document.getElementById('occStatus');
    const mapStatus = document.getElementById('occMapStatus');
    const latInput = document.getElementById('lat');
    const lngInput = document.getElementById('lng');
    const query = document.getElementById('occAddress');
    const searchButton = document.getElementById('occSearch');
    const results = document.getElementById('occResults');
    const currentLocationButton = document.getElementById('useLocation');
    const clearButton = document.getElementById('occClear');
    const records = <?= json_encode($recentes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]' ?>;
    if (typeof L === 'undefined') {
        mapElement.textContent = 'Não foi possível carregar o mapa. Confira sua conexão com a internet.';
        if (mapStatus) mapStatus.textContent = 'Mapa indisponível. Tente novamente mais tarde.';
        return;
    }
    const map = L.map(mapElement, {scrollWheelZoom:false}).setView([-8.7612,-63.9004], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom:19, attribution:'&copy; OpenStreetMap'
    }).addTo(map);
    const escapeHtml = v => String(v == null ? '' : v).replace(/[&<>"']/g, c =>
        ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const recordBounds = [];
    records.forEach(o => {
        const la = Number(o.latitude), lo = Number(o.longitude);
        if (!Number.isFinite(la) || !Number.isFinite(lo)) return;
        const pin = L.circleMarker([la,lo], {
            radius:8, color:'#9f1239', fillColor:'#e11d48', fillOpacity:0.9, weight:2
        }).addTo(map);
        pin.bindPopup('<strong>' + escapeHtml(o.nome_tipo) + '</strong><p>' +
            escapeHtml(o.descricao || 'Sem descrição') + '</p><small>' +
            escapeHtml(o.data) + ' ' + escapeHtml(o.horario) + '</small>');
        pin.on('click', event => { if (event.originalEvent) L.DomEvent.stopPropagation(event.originalEvent); });
        recordBounds.push([la,lo]);
    });
    if (recordBounds.length) map.fitBounds(recordBounds, {padding:[35,35],maxZoom:15});
    let selectedMarker = null;
    let lastSearchTime = 0;
    let activeSearch = null;
    function message(value, valid=false) {
        if (status) {
            status.textContent=value;
            status.classList.toggle('is-ready', valid);
        }
        if (mapStatus) mapStatus.textContent=value;
    }
    function hideResults() {
        if (!results) return;
        results.hidden=true;
        results.replaceChildren();
    }
    function selectPlace(latitude, longitude, label, zoom=true) {
        if (!form) return;
        const la=Number(latitude), lo=Number(longitude);
        if (!Number.isFinite(la) || !Number.isFinite(lo) || la < -90 || la > 90 || lo < -180 || lo > 180) return;
        latInput.value=la.toFixed(7);
        lngInput.value=lo.toFixed(7);
        if (!selectedMarker) {
            selectedMarker=L.marker([la,lo], {draggable:true, title:'Local da ocorrência. Arraste para ajustar.'}).addTo(map);
            selectedMarker.on('dragend', () => {
                const where=selectedMarker.getLatLng();
                selectPlace(where.lat,where.lng,'Local ajustado no mapa',false);
            });
        } else selectedMarker.setLatLng([la,lo]);
        if (zoom) map.setView([la,lo],Math.max(15,map.getZoom()));
        message('✅ Local escolhido: ' + label + '. Você pode ajustar arrastando o marcador azul.',true);
    }
    map.on('click', e => selectPlace(e.latlng.lat,e.latlng.lng,'Ponto marcado no mapa',false));
    if (latInput && lngInput && latInput.value !== '' && lngInput.value !== '') {
        selectPlace(latInput.value,lngInput.value,'Local informado anteriormente',true);
    }
    if (currentLocationButton && form) {
        currentLocationButton.addEventListener('click', () => {
            if (!navigator.geolocation) {
                message('Seu navegador não oferece localização. Escolha um ponto no mapa.');
                return;
            }
            currentLocationButton.disabled=true;
            message('Solicitando sua permissão para obter a localização...');
            navigator.geolocation.getCurrentPosition(
                p => {
                    currentLocationButton.disabled=false;
                    selectPlace(p.coords.latitude,p.coords.longitude,'Minha localização (confira se é o ponto correto)');
                },
                () => {
                    currentLocationButton.disabled=false;
                    message('Não foi possível obter sua localização. Pesquise um lugar ou escolha no mapa.');
                },
                {enableHighAccuracy:false,timeout:12000,maximumAge:60000}
            );
        });
    }
    if (clearButton && form) {
        clearButton.addEventListener('click', () => {
            latInput.value=''; lngInput.value='';
            if (selectedMarker) {map.removeLayer(selectedMarker); selectedMarker=null;}
            if (query) query.value='';
            hideResults();
            message('Nenhum local selecionado. Pesquise um lugar ou clique no mapa.');
        });
    }
    async function searchPlace() {
        if (!form || !query || !searchButton || !results) return;
        const term=query.value.trim();
        if (term.length < 3) {
            message('Digite ao menos 3 letras para pesquisar uma rua ou lugar.');
            query.focus();
            return;
        }
        if (Date.now()-lastSearchTime<1100) {
            message('Aguarde um instante antes de fazer outra busca.');
            return;
        }
        lastSearchTime=Date.now();
        if (activeSearch) activeSearch.abort();
        const controller=new AbortController();
        activeSearch=controller;
        const timeout=setTimeout(()=>controller.abort(),12000);
        hideResults();
        searchButton.disabled=true;
        message('Pesquisando lugares no OpenStreetMap...');
        try {
            const params=new URLSearchParams({
                q:term,format:'json',addressdetails:'1',limit:'5',countrycodes:'br',
                viewbox:'-64.3,-8.4,-63.4,-9.3',bounded:'0'
            });
            const response=await fetch('https://nominatim.openstreetmap.org/search?'+params.toString(), {
                signal:controller.signal,headers:{'Accept':'application/json'}
            });
            if (!response.ok) throw new Error('Busca indisponível');
            const places=await response.json();
            if (!Array.isArray(places) || !places.length) {
                message('Nenhum resultado encontrado. Inclua a cidade no endereço ou clique no mapa.');
                return;
            }
            for (const place of places) {
                const la=Number(place.lat),lo=Number(place.lon);
                if (!Number.isFinite(la)||!Number.isFinite(lo)) continue;
                const li=document.createElement('li');
                const button=document.createElement('button');
                button.type='button';button.className='occ-result-button';
                const name=String(place.display_name||'Local encontrado');
                button.textContent='📍 '+name;
                button.addEventListener('click',()=>{
                    query.value=name;
                    selectPlace(la,lo,name);
                    hideResults();
                });
                li.appendChild(button);results.appendChild(li);
            }
            results.hidden=false;
            message('Escolha um resultado abaixo para posicionar o marcador no mapa.');
        } catch (err) {
            message('A busca de endereço não respondeu. Você pode selecionar o local clicando no mapa.');
        } finally {
            clearTimeout(timeout);
            if (activeSearch===controller) {
                activeSearch=null;searchButton.disabled=false;
            }
        }
    }
    if (searchButton) searchButton.addEventListener('click',searchPlace);
    if (query) query.addEventListener('keydown',event=>{
        if (event.key==='Enter') {event.preventDefault();searchPlace();}
    });
    if (form) form.addEventListener('submit', event=>{
        if (!latInput.value || !lngInput.value) {
            event.preventDefault();
            message('Antes de enviar, selecione o local da ocorrência pelo mapa ou pela pesquisa.');
            if (query) query.focus();
        }
    });
    setTimeout(()=>map.invalidateSize(),200);
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
