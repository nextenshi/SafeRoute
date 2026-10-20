<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Admin — Mapa';
?>
<?php $extra_css=['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css']; $extra_js=['https://unpkg.com/leaflet@1.9.4/dist/leaflet.js']; ?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-brand"><strong>🛡️ SafeRoute</strong><span>Área administrativa</span></div>
    <nav class="admin-menu"><a class="" href="dashboard.php">📊<span>Dashboard</span></a>
<a class="" href="registros.php">⚠️<span>Registros</span></a>
<a class="" href="usuarios.php">👥<span>Usuários</span></a>
<a class="" href="tipos.php">🏷️<span>Tipos de ocorrência</span></a>
<a class="active" href="mapa.php">🗺️<span>Mapa</span></a>
<a class="" href="estatisticas.php">📈<span>Estatísticas</span></a></nav>
  </aside>
  <main class="admin-content">
    
<?php
$pdo=getConnection();
$ocorrencias=$pdo->query("SELECT o.id_ocorrencia,o.latitude,o.longitude,o.descricao,o.data,o.horario,o.status,t.nome_tipo,u.nome FROM ocorrencia o JOIN tipo_ocorrencia t ON t.id_tipo=o.id_tipo JOIN usuario u ON u.id_usuario=o.id_usuario ORDER BY o.id_ocorrencia DESC")->fetchAll();
$extra_css=['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'];
$extra_js=['https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'];
?>
<header class="admin-top"><div><span class="eyebrow">ADMINISTRAÇÃO</span><h1>Mapa de registros</h1><p>Visualize a localização dos registros cadastrados.</p></div></header>
<section class="admin-panel"><div id="adminMap" class="admin-map"></div><div class="map-legend"><span>📍 Marcadores representam ocorrências cadastradas.</span><span>O mapa usa OpenStreetMap.</span></div></section>
<script>
document.addEventListener('DOMContentLoaded',function(){
 const data=<?= json_encode($ocorrencias,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE) ?>;
 const map=L.map('adminMap').setView([-8.7612,-63.9004],13);
 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap'}).addTo(map);
 const bounds=[];
 data.forEach(o=>{
  const lat=Number(o.latitude),lng=Number(o.longitude);if(!Number.isFinite(lat)||!Number.isFinite(lng))return;
  const m=L.marker([lat,lng]).addTo(map);
  m.bindPopup('<strong>'+escapeHtml(o.nome_tipo)+'</strong><br>Usuário: '+escapeHtml(o.nome)+'<br>'+escapeHtml(o.descricao||'Sem descrição')+'<br><small>Status: '+escapeHtml(o.status)+'</small>');
  bounds.push([lat,lng]);
 });
 if(bounds.length)map.fitBounds(bounds,{padding:[30,30],maxZoom:16});
});
function escapeHtml(v){return String(v).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));}
</script>

  </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
