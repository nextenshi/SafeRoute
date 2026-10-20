<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Admin — Estatísticas';
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-brand"><strong>🛡️ SafeRoute</strong><span>Área administrativa</span></div>
    <nav class="admin-menu"><a class="" href="dashboard.php">📊<span>Dashboard</span></a>
<a class="" href="registros.php">⚠️<span>Registros</span></a>
<a class="" href="usuarios.php">👥<span>Usuários</span></a>
<a class="" href="tipos.php">🏷️<span>Tipos de ocorrência</span></a>
<a class="" href="mapa.php">🗺️<span>Mapa</span></a>
<a class="active" href="estatisticas.php">📈<span>Estatísticas</span></a></nav>
  </aside>
  <main class="admin-content">
    
<?php
$pdo=getConnection();
$porStatus=$pdo->query("SELECT status,COUNT(*) total FROM ocorrencia GROUP BY status ORDER BY total DESC")->fetchAll();
$porTipo=$pdo->query("SELECT t.nome_tipo,COUNT(o.id_ocorrencia) total FROM tipo_ocorrencia t LEFT JOIN ocorrencia o ON o.id_tipo=t.id_tipo GROUP BY t.id_tipo,t.nome_tipo ORDER BY total DESC")->fetchAll();
?>
<header class="admin-top"><div><span class="eyebrow">ADMINISTRAÇÃO</span><h1>Estatísticas</h1><p>Indicadores básicos do sistema para acompanhamento administrativo.</p></div></header>
<section class="admin-kpis"><?php foreach($porStatus as $s): ?><div class="kpi-box"><span><?= htmlspecialchars(ucfirst($s['status'])) ?></span><strong><?= (int)$s['total'] ?></strong></div><?php endforeach; ?></section>
<section class="admin-panel"><h2>Ocorrências por tipo</h2><p class="panel-subtitle">Quantidade de registros em cada categoria.</p><div style="overflow:auto"><table class="admin-table"><thead><tr><th>Tipo</th><th>Total</th></tr></thead><tbody><?php foreach($porTipo as $t): ?><tr><td><?= htmlspecialchars($t['nome_tipo']) ?></td><td><?= (int)$t['total'] ?></td></tr><?php endforeach; ?></tbody></table></div></section>

  </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
