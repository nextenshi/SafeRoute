/* SafeRoute — modos de deslocamento, rotas e transporte coletivo.
 * Carro e moto (estimativa automobilística): OSRM público.
 * A pé e bicicleta: endpoints comunitários OSRM/FOSSGIS especializados.
 * Ônibus: paradas e linhas colaborativas OSM via Overpass; integração externa
 * para tentativa de itinerário, sem inventar horários ou baldeações.
 * Serviços de demonstração, dependem de internet e podem ter limites.
 */
(() => {
    'use strict';

    const form = document.getElementById('routePlannerForm');
    if (!form) return;
    const byId = id => document.getElementById(id);
    const state = {
        origin: {
            lat: byId('originLat'), lng: byId('originLng'), text: byId('routeOrigin'),
            status: byId('originStatus'), marker: null,
        },
        destination: {
            lat: byId('destinationLat'), lng: byId('destinationLng'), text: byId('routeDestination'),
            status: byId('destinationStatus'), marker: null,
        },
    };
    const help = byId('routeHelp');
    const status = byId('routeRoadStatus');
    const summary = byId('routeRoadSummary');
    const directions = byId('routeDirectionsPanel');
    const list = byId('routeDirectionsList');
    const externalNavigation = byId('openGoogleMaps');
    const transportInput = byId('routeTransport');
    const transportButtons = Array.from(document.querySelectorAll('[data-transport]'));
    const transportBadge = byId('routeModeBadge');
    const routingNote = byId('routeRoutingNote');
    const busSection = byId('routeBusSection');
    const busStatus = byId('routeBusStatus');
    const loadBusButton = byId('loadBusData');
    const showBusStops = byId('showBusStops');
    const viewStopsOnGoogle = byId('viewStopsOnGoogle');
    const showRiskMarkers = byId('showRiskMarkers');
    const stopsList = byId('routeBusStops');
    const linesList = byId('routeBusLines');
    const pickButtons = Array.from(document.querySelectorAll('[data-pick]'));
    const searchButtons = Array.from(document.querySelectorAll('[data-search]'));

    let choosing = 'origin';
    let map, resultMap;
    let startLine = null, resultStartLine = null, routeLayer = null, resultRouteLayer = null;
    let lastGeocodeRequest = 0, geocoding = false;
    let lastRouteRequest = 0, routeTimer = null, routeController = null, routeVersion = 0;
    const modes = {
        carro: {label: '🚗 De carro', profile: 'carro', google: 'driving', icon: '🚗',
            note: 'Trajeto para carro calculado pelas ruas, sem informações de trânsito em tempo real.'},
        moto: {label: '🏍️ De moto', profile: 'carro', google: 'two-wheeler', icon: '🏍️',
            note: 'Estimativa de moto baseada no trajeto e tempo de CARRO. Não usa regras específicas para motocicletas; o Google Maps pode oferecer o modo moto onde estiver disponível.'},
        pe: {label: '🚶 A pé', profile: 'pe', google: 'walking', icon: '🚶',
            note: 'Caminho estimado para pedestres. A base cartográfica pode não conter todas as calçadas e travessias; confirme condições reais.'},
        bicicleta: {label: '🚲 De bicicleta', profile: 'bicicleta', google: 'bicycling', icon: '🚲',
            note: 'Trajeto estimado para bicicleta. A base pode não refletir todas as ciclovias ou condições atuais.'},
        onibus: {label: '🚌 De ônibus', profile: null, google: 'transit', icon: '🚌',
            note: 'Consulte paradas e linhas do OpenStreetMap. O SafeRoute não calcula viagens, baldeações e horários de ônibus; consulte o serviço de transporte oficial.'},
    };
    let mode = Object.hasOwn(modes, transportInput.value) ? transportInput.value : 'carro';
    let busStopsLayer = null, busRouteLayer = null, busController = null, busRouteController = null;
    let busRequestId = 0, busUpdateTimer = null, busBusy = false;
    let lastBusSearchCenter = null, lastBusSearchTime = 0, busDataLoaded = false;
    let busStopIndex = new Map(), busLinesIndex = new Map(), busStopMarkers = new Map();
    let riskLayer = null, resultRiskLayer = null;
    const busEndpoints = [
        'https://overpass.private.coffee/api/interpreter',
        'https://overpass-api.de/api/interpreter',
        'https://overpass.kumi.systems/api/interpreter'
    ];

    const pointPosition = mode => [Number(state[mode].lat.value), Number(state[mode].lng.value)];
    const selected = mode => {
        const p = state[mode];
        return p.lat.value.trim() !== '' && p.lng.value.trim() !== ''
            && Number.isFinite(Number(p.lat.value)) && Number.isFinite(Number(p.lng.value))
            && Math.abs(Number(p.lat.value)) <= 90 && Math.abs(Number(p.lng.value)) <= 180;
    };
    const explain = message => { help.textContent = message; };

    function chooseMode(mode) {
        choosing = mode;
        pickButtons.forEach(button => {
            const active = button.dataset.pick === mode;
            button.classList.toggle('is-selected', active);
            button.setAttribute('aria-pressed', String(active));
        });
        explain(mode === 'origin' ? 'Clique no mapa para marcar a origem.' : 'Clique no mapa para marcar o destino.');
    }

    function removeLayer(activeMap, layer) {
        if (activeMap && layer) activeMap.removeLayer(layer);
        return null;
    }

    function clearRouteLines() {
        routeLayer = removeLayer(map, routeLayer);
        resultRouteLayer = removeLayer(resultMap, resultRouteLayer);
        startLine = removeLayer(map, startLine);
        resultStartLine = removeLayer(resultMap, resultStartLine);
    }

    // Mostra uma linha pontilhada cinza só enquanto ainda não existe trajeto pelas ruas.
    function showWaitingLine() {
        clearRouteLines();
        if (mode === 'onibus' || !selected('origin') || !selected('destination')) return;
        const points = [pointPosition('origin'), pointPosition('destination')];
        startLine = L.polyline(points, {color: '#94a3b8', weight: 3, dashArray: '6 9', opacity: .7}).addTo(map);
        if (resultMap) {
            resultStartLine = L.polyline(points, {color: '#94a3b8', weight: 3, dashArray: '6 9', opacity: .7}).addTo(resultMap);
        }
    }

    function resetRouteDisplay() {
        if (routeTimer !== null) clearTimeout(routeTimer);
        routeTimer = null;
        if (routeController) routeController.abort();
        routeVersion += 1;
        summary.hidden = true;
        directions.hidden = true;
        list.replaceChildren();
        showWaitingLine();
    }

    const formatDistance = meters => meters < 1000
        ? `${Math.round(meters)} m`
        : `${(meters / 1000).toLocaleString('pt-BR', {maximumFractionDigits: 1})} km`;
    function formatDuration(seconds) {
        const minutes = Math.max(1, Math.round(seconds / 60));
        if (minutes < 60) return `${minutes} min`;
        const h = Math.floor(minutes / 60), m = minutes % 60;
        return m ? `${h} h ${m} min` : `${h} h`;
    }

    const directionsPt = {
        'left': 'à esquerda', 'slight left': 'levemente à esquerda', 'sharp left': 'acentuadamente à esquerda',
        'right': 'à direita', 'slight right': 'levemente à direita', 'sharp right': 'acentuadamente à direita',
        'straight': 'em frente', 'uturn': 'para fazer o retorno',
    };
    function roadName(step) {
        const name = (step.name || step.ref || '').trim();
        return name ? ` na ${name}` : '';
    }
    function stepInstruction(step) {
        const maneuver = step.maneuver || {};
        const type = maneuver.type || 'turn';
        const side = directionsPt[maneuver.modifier] || 'em frente';
        const street = roadName(step);
        switch (type) {
            case 'depart': return `Saia da origem e siga${street || ' pelo trajeto indicado'}.`;
            case 'arrive': return 'Você chegou ao destino.';
            case 'roundabout':
            case 'rotary': return maneuver.exit
                ? `Na rotatória, pegue a ${maneuver.exit}ª saída${street}.`
                : `Entre na rotatória${street}.`;
            case 'exit roundabout':
            case 'exit rotary': return `Saia da rotatória${street}.`;
            case 'merge': return `Entre na via ${side}${street}.`;
            case 'on ramp': return `Pegue a alça de acesso ${side}${street}.`;
            case 'off ramp': return `Pegue a saída ${side}${street}.`;
            case 'fork': return `Mantenha-se ${side}${street}.`;
            case 'end of road': return `No fim da rua, vire ${side}${street}.`;
            case 'new name': return `Continue${street || ' na mesma via'}.`;
            case 'notification': return `Atenção à mudança de condições da via${street}.`;
            case 'continue': return `Continue ${side}${street}.`;
            case 'roundabout turn': return `Na rotatória, vire ${side}${street}.`;
            default: return `Vire ${side}${street}.`;
        }
    }
    function stepIcon(step) {
        const maneuver = step.maneuver || {};
        if (maneuver.type === 'depart') return modes[mode].icon;
        if (maneuver.type === 'arrive') return '🏁';
        if (['roundabout', 'rotary', 'exit roundabout', 'exit rotary'].includes(maneuver.type)) return '↻';
        if (maneuver.modifier === 'uturn') return '↶';
        if ((maneuver.modifier || '').includes('left')) return '↰';
        if ((maneuver.modifier || '').includes('right')) return '↱';
        return '↑';
    }

    function showSteps(route) {
        list.replaceChildren();
        const steps = Array.isArray(route.legs)
            ? route.legs.flatMap(leg => Array.isArray(leg.steps) ? leg.steps : []) : [];
        if (!steps.length) {
            directions.hidden = true;
            return;
        }
        steps.forEach((step, index) => {
            const item = document.createElement('li');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'route-direction-step';
            button.title = 'Mostrar este trecho no mapa';
            button.setAttribute('aria-label', `Etapa ${index + 1}: ${stepInstruction(step)} ${formatDistance(step.distance || 0)}`);
            const icon = document.createElement('span');
            icon.className = 'route-direction-icon';
            icon.textContent = stepIcon(step);
            const contents = document.createElement('span');
            contents.className = 'route-direction-content';
            const text = document.createElement('strong');
            text.textContent = stepInstruction(step);
            const info = document.createElement('small');
            info.textContent = formatDistance(step.distance || 0);
            contents.append(text, info);
            button.append(icon, contents);
            const location = step.maneuver && step.maneuver.location;
            if (Array.isArray(location) && location.length === 2) {
                button.addEventListener('click', () => {
                    map.setView([location[1], location[0]], 17);
                    map.openPopup(stepInstruction(step), [location[1], location[0]]);
                    if (resultMap) resultMap.setView([location[1], location[0]], 17);
                });
            } else button.disabled = true;
            item.append(button);
            list.append(item);
        });
        directions.hidden = false;
    }

    function showRoadRoute(route) {
        clearRouteLines();
        const geometry = route.geometry;
        routeLayer = L.geoJSON(geometry, {style: {color: '#0284c7', weight: 6, opacity: .9}}).addTo(map);
        if (resultMap) {
            resultRouteLayer = L.geoJSON(geometry, {style: {color: '#0284c7', weight: 6, opacity: .9}}).addTo(resultMap);
            resultMap.fitBounds(resultRouteLayer.getBounds(), {padding: [34, 34], maxZoom: 16});
        }
        map.fitBounds(routeLayer.getBounds(), {padding: [35, 35], maxZoom: 16});
        byId('roadDistance').textContent = formatDistance(route.distance);
        byId('roadDuration').textContent = formatDuration(route.duration);
        summary.hidden = false;
        status.textContent = mode === 'moto'
            ? 'Rota aproximada usando o perfil de carro (não é uma rota exclusiva para motos). Toque em uma instrução para ver a conversão.'
            : 'Rota encontrada! A linha azul acompanha o caminho estimado. Toque em uma instrução para ver a conversão.';
        showSteps(route);
    }

    async function fetchStreetRoute(version) {
        if (version !== routeVersion || !selected('origin') || !selected('destination')) return;
        if (routeController) routeController.abort();
        const controller = new AbortController();
        routeController = controller;
        // O roteador demonstrativo público permite no máximo uma solicitação por segundo.
        const waitMs = Math.max(0, 1150 - (Date.now() - lastRouteRequest));
        if (waitMs) await new Promise(resolve => setTimeout(resolve, waitMs));
        if (version !== routeVersion || controller.signal.aborted) return;
        lastRouteRequest = Date.now();
        const [aLat, aLng] = pointPosition('origin');
        const [bLat, bLng] = pointPosition('destination');
        const coordinates = `${aLng},${aLat};${bLng},${bLat}`;
        const endpoint = mode === 'pe' ? 'https://routing.openstreetmap.de/routed-foot/route/v1/driving/'
            : mode === 'bicicleta' ? 'https://routing.openstreetmap.de/routed-bike/route/v1/driving/'
            : 'https://router.project-osrm.org/route/v1/driving/';
        const url = `${endpoint}${coordinates}?overview=full&geometries=geojson&steps=true&alternatives=false`;
        try {
            const response = await fetch(url, {signal: controller.signal, headers: {'Accept': 'application/json'}});
            if (!response.ok) throw new Error('Serviço de rotas indisponível');
            const data = await response.json();
            if (version !== routeVersion) return;
            if (data.code !== 'Ok' || !data.routes || !data.routes.length) {
                status.textContent = 'Não foi encontrada uma rota nesse modo. Tente selecionar locais mais próximos às vias ou abra no Google Maps.';
                return;
            }
            const route = data.routes[0];
            if (!route.geometry || !Number.isFinite(route.distance) || !Number.isFinite(route.duration)) throw new Error('Resposta incompleta');
            showRoadRoute(route);
        } catch (error) {
            if (error.name === 'AbortError' || version !== routeVersion) return;
            status.textContent = 'O serviço de rotas desse modo não respondeu. Verifique a internet ou abra o trajeto no Google Maps. A linha cinza não representa ruas.';
        }
    }

    function scheduleRoute() {
        resetRouteDisplay();
        externalNavigation.hidden = !selected('origin') || !selected('destination');
        if (!externalNavigation.hidden) {
            const origin = pointPosition('origin').join(',');
            const destination = pointPosition('destination').join(',');
            const parameters = new URLSearchParams({api: '1', origin, destination, travelmode: modes[mode].google});
            externalNavigation.href = `https://www.google.com/maps/dir/?${parameters.toString()}`;
        } else {
            externalNavigation.removeAttribute('href');
        }
        if (!selected('origin') || !selected('destination')) {
            status.textContent = mode === 'onibus'
                ? 'Paradas de ônibus são exibidas no mapa mesmo sem origem e destino. Escolha dois pontos se quiser consultar um itinerário externo.'
                : 'Selecione a origem e o destino para calcular o caminho pelas ruas.';
            return;
        }
        if (mode === 'onibus') {
            status.textContent = 'Ônibus: paradas amarelas e traçados roxos no próprio mapa. A lista abaixo mostra linhas mapeadas; consulte o PVH+ para horários oficiais.';
            return;
        }
        status.textContent = `Calculando rota ${mode === 'moto' ? 'aproximada (perfil de carro)' : modes[mode].label.toLowerCase()}…`;
        const version = routeVersion;
        routeTimer = setTimeout(() => fetchStreetRoute(version), 700);
    }

    function setTransport(next) {
        if (!Object.hasOwn(modes, next)) return;
        const changed = mode !== next;
        mode = next;
        transportInput.value = next;
        transportBadge.textContent = modes[next].label;
        routingNote.textContent = modes[next].note;
        busSection.hidden = next !== 'onibus';
        transportButtons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.transport === next)));
        if (changed) {
            // Não apaga as paradas: elas devem aparecer em todos os meios de transporte.
            scheduleRoute();
            if (next === 'onibus' && map && !busDataLoaded && !busBusy) scheduleBusRefresh();
        }
    }

    // Os dados do OpenStreetMap são colaborativos: um registro próximo de uma parada
    // não prova que o ônibus pare nela. Vínculos explícitos usam relation.members.
    function stopKey(stop) { return `${stop.type}/${stop.id}`; }
    function getStopPosition(stop) {
        const lat = Number(stop.lat ?? stop.center?.lat);
        const lng = Number(stop.lon ?? stop.center?.lon);
        return Number.isFinite(lat) && Number.isFinite(lng) ? [lat, lng] : null;
    }
    function clearBusResults() {
        busRequestId++;
        if (busUpdateTimer) clearTimeout(busUpdateTimer);
        busUpdateTimer = null;
        if (busController) { busController.abort(); busController = null; }
        if (busRouteController) { busRouteController.abort(); busRouteController = null; }
        if (busStopsLayer) map.removeLayer(busStopsLayer);
        if (busRouteLayer) map.removeLayer(busRouteLayer);
        busStopsLayer = null;
        busRouteLayer = null;
        busStopIndex.clear(); busLinesIndex.clear(); busStopMarkers.clear();
        stopsList.replaceChildren(); linesList.replaceChildren();
        busBusy = false;
        loadBusButton.disabled = false;
        loadBusButton.textContent = '↻ Buscar paradas nesta região';
    }

    function listEmpty(container, message) {
        const li = document.createElement('li');
        li.textContent = message;
        container.append(li);
    }

    function busLabel(tags) {
        return tags.name || tags.ref || tags.local_ref || 'Parada sem nome cadastrado';
    }
    function lineLabel(route) {
        const tags = route.tags || {};
        const parts = [tags.ref, tags.name].filter(Boolean);
        return parts.join(' — ') || `Linha OSM ${route.id}`;
    }
    function linkedRoutes(stop) {
        const key = stopKey(stop);
        return Array.from(busLinesIndex.values()).filter(route =>
            Array.isArray(route.members) && route.members.some(member =>
                `${member.type}/${member.ref}` === key));
    }
    function stopPopup(stop) {
        const wrap = document.createElement('div');
        const heading = document.createElement('strong');
        heading.textContent = '🚌 ' + busLabel(stop.tags || {});
        wrap.append(heading);
        const linked = linkedRoutes(stop);
        const detail = document.createElement('p');
        detail.textContent = linked.length
            ? 'Linhas vinculadas a esta parada no OpenStreetMap:'
            : 'Nenhuma linha vinculada a esta parada nos dados consultados. Isso não significa que não passe ônibus.';
        wrap.append(detail);
        linked.slice(0, 15).forEach(route => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'route-stop-line';
            item.textContent = '↗ ' + lineLabel(route);
            item.addEventListener('click', () => {
                if (mode !== 'onibus') setTransport('onibus');
                viewBusRoute(route.id, lineLabel(route));
            });
            wrap.append(item);
        });
        return wrap;
    }
    function appendStop(stop) {
        const position = getStopPosition(stop);
        if (!position) return false;
        const key = stopKey(stop);
        const marker = L.circleMarker(position, {
            radius: 7, color: '#92400e', weight: 2, fillColor: '#facc15', fillOpacity: .95,
        });
        marker.bindPopup(() => stopPopup(stop));
        busStopsLayer.addLayer(marker);
        busStopMarkers.set(key, marker);
        const li = document.createElement('li');
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = '🚌 ' + busLabel(stop.tags || {});
        button.addEventListener('click', () => {
            if (!map.hasLayer(busStopsLayer)) { busStopsLayer.addTo(map); showBusStops.checked = true; }
            map.setView(position, 17);
            marker.openPopup();
        });
        li.append(button);
        const linked = linkedRoutes(stop);
        const small = document.createElement('small');
        small.textContent = linked.length
            ? `Linha(s) vinculada(s): ${linked.slice(0, 5).map(lineLabel).join('; ')}`
            : 'Linhas nesta parada: não informadas no OSM';
        li.append(small);
        stopsList.append(li);
        return true;
    }

    // Tenta serviços alternativos para reduzir falhas na busca de paradas.
    // Mantém as chamadas sequenciais para não sobrecarregar a infraestrutura voluntária.
    async function overpassFetch(query, signal) {
        let lastError = null;
        for (const endpoint of busEndpoints) {
            if (signal.aborted) throw new DOMException('Requisição cancelada', 'AbortError');
            const timeout = new AbortController();
            const timer = setTimeout(() => timeout.abort(), 12000);
            const abort = () => timeout.abort();
            signal.addEventListener('abort', abort, {once: true});
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: new URLSearchParams({data: query}),
                    headers: {'Accept': 'application/json'},
                    signal: timeout.signal,
                });
                if (!response.ok) throw new Error('HTTP ' + response.status);
                const data = await response.json();
                if (!Array.isArray(data.elements)) throw new Error('Resposta incompleta do serviço');
                return data.elements;
            } catch (error) {
                if (signal.aborted) throw new DOMException('Requisição cancelada', 'AbortError');
                lastError = error;
            } finally {
                clearTimeout(timer);
                signal.removeEventListener('abort', abort);
            }
        }
        throw lastError || new Error('Serviços de dados indisponíveis');
    }

    async function viewBusRoute(routeId, name) {
        if (!Number.isSafeInteger(routeId) || routeId < 1) return;
        if (busRouteController) busRouteController.abort();
        busRouteController = new AbortController();
        const controller = busRouteController;
        busStatus.textContent = `Carregando o traçado mapeado da linha ${name}…`;
        try {
            const query = `[out:json][timeout:20];relation(${routeId});way(r);out geom;`;
            const elements = await overpassFetch(query, controller.signal);
            if (controller.signal.aborted || mode !== 'onibus') return;
            if (busRouteLayer) map.removeLayer(busRouteLayer);
            busRouteLayer = L.featureGroup();
            let parts = 0;
            for (const way of elements) {
                if (way.type !== 'way' || !Array.isArray(way.geometry)) continue;
                const positions = way.geometry.map(p => [p.lat, p.lon]).filter(p => p.every(Number.isFinite));
                if (positions.length < 2) continue;
                L.polyline(positions, {color: '#7c3aed', weight: 5, opacity: .85}).addTo(busRouteLayer);
                parts++;
            }
            if (!parts) {
                busStatus.textContent = `A linha ${name} não tem trajeto desenhado disponível no OSM.`;
                return;
            }
            busRouteLayer.addTo(map);
            map.fitBounds(busRouteLayer.getBounds(), {padding: [30, 30], maxZoom: 14});
            busStatus.textContent = `Traçado roxo: ruas cadastradas da linha ${name} no OSM. O sentido e os horários não são garantidos.`;
        } catch (error) {
            if (error.name !== 'AbortError' && !controller.signal.aborted) {
                busStatus.textContent = 'Não foi possível carregar o trajeto desta linha agora. Tente novamente mais tarde.';
            }
        }
    }

    function appendBusLine(route) {
        const tags = route.tags || {};
        const li = document.createElement('li');
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = '🚌 ' + lineLabel(route);
        button.addEventListener('click', () => viewBusRoute(route.id, lineLabel(route)));
        li.append(button);
        const linkedCount = Array.from(busStopIndex.values()).filter(stop => linkedRoutes(stop)
            .some(r => r.id === route.id)).length;
        const small = document.createElement('small');
        small.textContent = linkedCount
            ? `Vinculada a ${linkedCount} parada(s) mostrada(s). Clique para visualizar as ruas mapeadas.`
            : 'Linha mapeada nesta região. Não há vínculo confirmado com as paradas mostradas.';
        li.append(small);
        if (tags.operator || tags.network) {
            const meta = document.createElement('small');
            meta.textContent = [tags.operator, tags.network].filter(Boolean).join(' · ');
            li.append(meta);
        }
        linesList.append(li);
    }

    function busSearchCenters() {
        const centers = [];
        const add = (lat, lng, radius) => {
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
            if (centers.some(([y, x]) => Math.abs(y - lat) < .004 && Math.abs(x - lng) < .004)) return;
            centers.push([Number(lat.toFixed(6)), Number(lng.toFixed(6)), radius]);
        };
        // Primeiro consulta a região exibida; se houver pontos, inclui os dois lados da viagem.
        const center = map.getCenter();
        add(center.lat, center.lng, map.getZoom() >= 15 ? 850 : 1600);
        for (const field of ['origin', 'destination']) {
            if (selected(field)) {
                const [lat, lng] = pointPosition(field);
                add(lat, lng, 850);
            }
        }
        return centers.slice(0, 3);
    }

    function scheduleBusRefresh() {
        if (!map || !showBusStops.checked || busBusy) return;
        if (busUpdateTimer) clearTimeout(busUpdateTimer);
        busUpdateTimer = setTimeout(() => loadBusData(), 850);
    }

    async function loadBusData() {
        // Consulta de paradas é independente da seleção de carro, bicicleta etc.
        if (busBusy) return;
        if (viewStopsOnGoogle) {
            const center = map.getCenter();
            viewStopsOnGoogle.href = 'https://www.google.com/maps/search/?' +
                new URLSearchParams({api: '1', query: `paradas de ônibus perto de ${center.lat.toFixed(5)},${center.lng.toFixed(5)}`});
        }
        clearBusResults();
        const selection = busRequestId;
        const controller = new AbortController();
        busController = controller;
        busBusy = true;
        lastBusSearchCenter = map.getCenter();
        lastBusSearchTime = Date.now();
        busDataLoaded = false;
        loadBusButton.disabled = true;
        loadBusButton.textContent = 'Buscando paradas…';
        busStatus.textContent = 'Procurando paradas na região do mapa. Pode demorar até alguns segundos…';
        const centers = busSearchCenters();
        const locations = centers.map(([lat, lng, radius]) => `
            node(around:${radius},${lat},${lng})["highway"="bus_stop"];
            node(around:${radius},${lat},${lng})["public_transport"="platform"]["bus"!="no"];
            way(around:${radius},${lat},${lng})["public_transport"="platform"]["bus"!="no"];`).join('');
        const stopQuery = `[out:json][timeout:20];(${locations});out center;`;
        try {
            const stopElements = await overpassFetch(stopQuery, controller.signal);
            if (selection !== busRequestId || mode !== 'onibus') return;
            stopElements.forEach(stop => {
                if (['node', 'way'].includes(stop.type) && getStopPosition(stop)) {
                    busStopIndex.set(stopKey(stop), stop);
                }
            });
            const stops = Array.from(busStopIndex.values()).slice(0, 100);
            busStatus.textContent = `${stops.length} parada(s) mapeada(s). Procurando linhas que passam na região…`;

            // Consulta independente: mesmo que as linhas falhem, os pontos já aparecem.
            busStopsLayer = L.layerGroup();
            for (const stop of stops) appendStop(stop);
            if (showBusStops.checked && stops.length) busStopsLayer.addTo(map);
            busDataLoaded = true;
            if (!stops.length) listEmpty(stopsList,
                'O OpenStreetMap não retornou paradas nesta região. Consulte o Google Maps ou os itinerários oficiais pelos links acima.');
            // Linhas próximas dos centros. A saída "body" conserva membros para identificar
            // quais paradas estão vinculadas explicitamente às relações de ônibus.
            const regionQueries = centers.map(([lat, lng]) =>
                `relation(around:750,${lat},${lng})["route"="bus"];`).join('');
            const lineQuery = `[out:json][timeout:20];(${regionQueries});out body;`;
            try {
                const lines = await overpassFetch(lineQuery, controller.signal);
                if (selection !== busRequestId || mode !== 'onibus') return;
                for (const route of lines) {
                    if (route.type === 'relation' && route.tags?.route === 'bus') busLinesIndex.set(route.id, route);
                }
                // Atualiza os popups/lista com dados de linhas agora disponíveis.
                stopsList.replaceChildren();
                busStopMarkers.forEach((marker, key) => {
                    const stop = busStopIndex.get(key);
                    if (stop) marker.bindPopup(() => stopPopup(stop));
                });
                for (const stop of stops) appendStopListOnly(stop);
                for (const route of Array.from(busLinesIndex.values()).slice(0, 60)) appendBusLine(route);
                if (!busLinesIndex.size) listEmpty(linesList,
                    'Nenhum itinerário foi encontrado no OpenStreetMap para a região.');
                busStatus.textContent = `${stops.length} parada(s) no mapa e ${busLinesIndex.size} linha(s) mapeada(s) na região. Clique em um ponto amarelo para conferir as linhas vinculadas.`;
            } catch (lineError) {
                if (lineError.name === 'AbortError' || selection !== busRequestId) return;
                listEmpty(linesList, 'As paradas carregaram, mas o serviço de linhas não respondeu. Você ainda pode ver os pontos amarelos no mapa.');
                busStatus.textContent = `${stops.length} parada(s) carregada(s). A consulta de linhas falhou; use Atualizar para tentar novamente.`;
            }
        } catch (error) {
            if (error.name !== 'AbortError' && selection === busRequestId) {
                busStatus.textContent = 'A consulta de paradas do OpenStreetMap falhou ou excedeu o tempo de espera. Use os links para consultar Google Maps ou o serviço oficial de ônibus, ou tente novamente.';
                listEmpty(stopsList, 'Dados temporariamente indisponíveis. Nenhuma parada fictícia foi adicionada ao mapa.');
            }
        } finally {
            if (selection === busRequestId) {
                busBusy = false;
                loadBusButton.disabled = false;
                loadBusButton.textContent = '↻ Buscar paradas nesta região';
            }
        }
    }

    function appendStopListOnly(stop) {
        const position = getStopPosition(stop);
        if (!position) return;
        const li = document.createElement('li');
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = '🚌 ' + busLabel(stop.tags || {});
        button.addEventListener('click', () => {
            if (busStopsLayer && !map.hasLayer(busStopsLayer)) {
                busStopsLayer.addTo(map); showBusStops.checked = true;
            }
            map.setView(position, 17);
            busStopMarkers.get(stopKey(stop))?.openPopup();
        });
        const small = document.createElement('small');
        const linked = linkedRoutes(stop);
        small.textContent = linked.length
            ? `Linha(s) vinculada(s): ${linked.slice(0, 5).map(lineLabel).join('; ')}`
            : 'Sem informação confirmada de linhas nesta parada';
        li.append(button, small);
        stopsList.append(li);
    }

    function setPoint(mode, lat, lng, label) {
        if (!Number.isFinite(lat) || !Number.isFinite(lng) || Math.abs(lat) > 90 || Math.abs(lng) > 180) return;
        const point = state[mode];
        point.lat.value = lat.toFixed(7);
        point.lng.value = lng.toFixed(7);
        if (label) point.text.value = label;
        if (!point.text.value.trim()) point.text.value = mode === 'origin' ? 'Ponto de origem no mapa' : 'Ponto de destino no mapa';
        if (point.marker) {
            point.marker.setLatLng([lat, lng]);
        } else {
            point.marker = L.marker([lat, lng], {draggable: true}).addTo(map)
                .bindPopup(mode === 'origin' ? 'Origem (arraste para ajustar)' : 'Destino (arraste para ajustar)');
            point.marker.on('dragend', () => {
                const position = point.marker.getLatLng();
                setPoint(mode, position.lat, position.lng,
                    mode === 'origin' ? 'Ponto de origem no mapa' : 'Ponto de destino no mapa');
            });
        }
        point.status.textContent = mode === 'origin' ? '✓ Origem selecionada.' : '✓ Destino selecionado.';
        point.status.classList.add('is-ready');
        scheduleRoute();
        scheduleBusRefresh();
        if (mode === 'origin' && !selected('destination')) chooseMode('destination');
        else explain('Ponto selecionado! Você pode mover o marcador ou consultar o caminho indicado pelas ruas.');
    }

    function clearPoint(mode) {
        const point = state[mode];
        point.lat.value = '';
        point.lng.value = '';
        point.status.textContent = mode === 'origin' ? 'Origem ainda não selecionada.' : 'Destino ainda não selecionado.';
        point.status.classList.remove('is-ready');
        if (point.marker) {
            map.removeLayer(point.marker);
            point.marker = null;
        }
        scheduleRoute();
        scheduleBusRefresh();
    }

    async function findPlace(mode, button) {
        const query = state[mode].text.value.trim();
        if (query.length < 3) {
            explain('Digite pelo menos 3 caracteres de um endereço ou lugar.');
            state[mode].text.focus();
            return;
        }
        if (geocoding) { explain('Espere a pesquisa anterior terminar.'); return; }
        geocoding = true;
        searchButtons.forEach(element => { element.disabled = true; });
        button.textContent = 'Buscando…';
        explain('Procurando o lugar no OpenStreetMap…');
        try {
            const waitMs = Math.max(0, 1100 - (Date.now() - lastGeocodeRequest));
            if (waitMs) await new Promise(resolve => setTimeout(resolve, waitMs));
            lastGeocodeRequest = Date.now();
            const request = new URL('https://nominatim.openstreetmap.org/search');
            request.searchParams.set('q', query);
            request.searchParams.set('format', 'jsonv2');
            request.searchParams.set('limit', '1');
            request.searchParams.set('countrycodes', 'br');
            request.searchParams.set('accept-language', 'pt-BR');
            const response = await fetch(request.toString(), {headers: {'Accept': 'application/json'}});
            if (!response.ok) throw new Error('Busca indisponível');
            const matches = await response.json();
            if (!Array.isArray(matches) || !matches.length) {
                explain('Lugar não encontrado. Inclua bairro e cidade ou escolha o ponto no mapa.');
                return;
            }
            const lat = Number(matches[0].lat), lng = Number(matches[0].lon);
            setPoint(mode, lat, lng, query);
            map.setView([lat, lng], 16);
            explain('Lugar encontrado! Confira a posição e arraste o marcador se necessário.');
        } catch (_) {
            explain('Não foi possível pesquisar agora. Confira a internet ou selecione diretamente no mapa.');
        } finally {
            geocoding = false;
            button.textContent = 'Buscar no mapa';
            searchButtons.forEach(element => { element.disabled = false; });
        }
    }

    if (!window.L) {
        explain('O mapa não carregou. Verifique a internet e atualize a página.');
        byId('routePickerMap').textContent = 'Mapa indisponível no momento.';
        status.textContent = 'Não foi possível carregar o mapa.';
        return;
    }

    function addBaseMap(target) {
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> colaboradores',
        }).addTo(target);
    }
    map = L.map('routePickerMap').setView([-8.7619, -63.9039], 13);
    addBaseMap(map);
    if (byId('routeMap')) {
        resultMap = L.map('routeMap').setView([-8.7619, -63.9039], 13);
        addBaseMap(resultMap);
    }
    // Pontos da análise: o servidor envia exatamente os registros usados no indicador.
    const riskSource = byId('routeRiskData');
    if (riskSource) {
        try {
            const occurrences = JSON.parse(riskSource.textContent || '[]');
            riskLayer = L.layerGroup();
            resultRiskLayer = resultMap ? L.layerGroup() : null;
            const buildMarker = item => {
                const lat = Number(item.latitude), lng = Number(item.longitude);
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
                const marker = L.circleMarker([lat, lng], {
                    radius: 9, color: '#881337', weight: 2, fillColor: '#e11d48', fillOpacity: .88,
                });
                const popup = document.createElement('div');
                const heading = document.createElement('strong');
                heading.textContent = '⚠️ ' + (item.nome_tipo || 'Ocorrência');
                const description = document.createElement('p');
                description.textContent = item.descricao || 'Sem descrição';
                const date = document.createElement('small');
                date.textContent = `Registrada em ${item.data || 'data não informada'}`;
                popup.append(heading, description, date);
                marker.bindPopup(popup);
                return marker;
            };
            occurrences.forEach(item => {
                const marker = buildMarker(item);
                if (marker) riskLayer.addLayer(marker);
                if (resultRiskLayer) {
                    const copy = buildMarker(item);
                    if (copy) resultRiskLayer.addLayer(copy);
                }
            });
            if (showRiskMarkers && showRiskMarkers.checked) {
                riskLayer.addTo(map);
                if (resultRiskLayer) resultRiskLayer.addTo(resultMap);
            }
            if (showRiskMarkers) showRiskMarkers.addEventListener('change', () => {
                if (showRiskMarkers.checked) {
                    riskLayer.addTo(map);
                    if (resultRiskLayer) resultRiskLayer.addTo(resultMap);
                } else {
                    map.removeLayer(riskLayer);
                    if (resultRiskLayer) resultMap.removeLayer(resultRiskLayer);
                }
            });
        } catch (_) { /* Resultado numérico continua visível se o JSON estiver indisponível. */ }
    }
    map.on('click', event => setPoint(choosing, event.latlng.lat, event.latlng.lng,
        choosing === 'origin' ? 'Ponto de origem no mapa' : 'Ponto de destino no mapa'));
    setTransport(mode);
    transportButtons.forEach(button => button.addEventListener('click', () => setTransport(button.dataset.transport)));
    loadBusButton.addEventListener('click', loadBusData);
    map.on('moveend', () => {
        const center = map.getCenter();
        if (viewStopsOnGoogle) viewStopsOnGoogle.href = 'https://www.google.com/maps/search/?' +
            new URLSearchParams({api: '1', query: `paradas de ônibus perto de ${center.lat.toFixed(5)},${center.lng.toFixed(5)}`});
        if (!showBusStops.checked || busBusy) return;
        // Atualiza apenas quando o usuário se desloca bastante no mapa e com intervalo
        // mínimo; evita consultas repetidas na infraestrutura gratuita do Overpass.
        const moved = !lastBusSearchCenter || center.distanceTo(lastBusSearchCenter) >= 850;
        const rested = Date.now() - lastBusSearchTime > 20000;
        if (moved && rested) scheduleBusRefresh();
        else if (moved) busStatus.textContent = 'Mapa movido: use “Buscar paradas nesta região” para atualizar os pontos.';
    });
    showBusStops.addEventListener('change', () => {
        if (!showBusStops.checked) {
            if (busStopsLayer) map.removeLayer(busStopsLayer);
        } else {
            if (busStopsLayer) busStopsLayer.addTo(map);
            else scheduleBusRefresh();
        }
    });
    pickButtons.forEach(button => button.addEventListener('click', () => chooseMode(button.dataset.pick)));
    searchButtons.forEach(button => button.addEventListener('click', () => findPlace(button.dataset.search, button)));

    for (const mode of ['origin', 'destination']) {
        const point = state[mode];
        point.text.addEventListener('input', () => clearPoint(mode));
        if (selected(mode)) setPoint(mode, Number(point.lat.value), Number(point.lng.value), point.text.value);
    }

    scheduleBusRefresh();
    if (selected('origin') && selected('destination')) {
        const bounds = [pointPosition('origin'), pointPosition('destination')];
        map.fitBounds(bounds, {padding: [35, 35], maxZoom: 16});
        if (resultMap) {
            L.marker(bounds[0]).addTo(resultMap).bindPopup('Origem');
            L.marker(bounds[1]).addTo(resultMap).bindPopup('Destino');
            resultMap.fitBounds(bounds, {padding: [35, 35], maxZoom: 16});
        }
    }

    byId('useMyLocation').addEventListener('click', () => {
        if (!navigator.geolocation) { explain('Este navegador não oferece localização. Use o mapa ou a busca.'); return; }
        explain('Solicitando acesso à sua localização…');
        navigator.geolocation.getCurrentPosition(position => {
            setPoint('origin', position.coords.latitude, position.coords.longitude, 'Minha localização');
            map.setView([position.coords.latitude, position.coords.longitude], 16);
            explain('Origem atual selecionada. Verifique no mapa se a posição está correta.');
        }, () => explain('Não foi possível obter a localização. Use a busca ou o mapa.'),
        {enableHighAccuracy: true, timeout: 12000, maximumAge: 0});
    });

    form.addEventListener('submit', event => {
        if (!selected('origin') || !selected('destination')) {
            event.preventDefault();
            explain('Marque origem e destino no mapa ou use Buscar no mapa.');
            help.scrollIntoView({behavior: 'smooth', block: 'center'});
        } else if (!state.origin.text.value.trim() || !state.destination.text.value.trim()) {
            event.preventDefault();
            explain('Informe a origem e o destino.');
        }
    });
})();
