<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Admin — Dashboard';
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-brand"><strong>🛡️ SafeRoute</strong><span>Área administrativa</span></div>
    <nav class="admin-menu"><a class="active" href="dashboard.php">📊<span>Dashboard</span></a>
<a class="" href="registros.php">⚠️<span>Registros</span></a>
<a class="" href="usuarios.php">👥<span>Usuários</span></a>
<a class="" href="tipos.php">🏷️<span>Tipos de ocorrência</span></a>
<a class="" href="mapa.php">🗺️<span>Mapa</span></a>
<a class="" href="estatisticas.php">📈<span>Estatísticas</span></a></nav>
  </aside>
  <main class="admin-content">
    
<header class="admin-top">
  <div><span class="eyebrow">PAINEL ADMINISTRATIVO</span><h1>Visão geral</h1><p>Acompanhe os principais dados do SafeRoute.</p></div>
  <div class="admin-actions"><a class="admin-btn primary" href="registros.php">Gerenciar registros</a><a class="admin-btn secondary" href="mapa.php">Abrir mapa</a></div>
</header>
<?php
$pdo = getConnection();
$totalUsuarios=(int)$pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn();
$totalOcorrencias=(int)$pdo->query("SELECT COUNT(*) FROM ocorrencia")->fetchColumn();
$totalRotas=(int)$pdo->query("SELECT COUNT(*) FROM rota")->fetchColumn();
$pendentes=(int)$pdo->query("SELECT COUNT(*) FROM ocorrencia WHERE status='pendente'")->fetchColumn();
$ultimos=$pdo->query("SELECT o.id_ocorrencia,o.descricao,o.data,o.horario,o.status,t.nome_tipo FROM ocorrencia o JOIN tipo_ocorrencia t ON t.id_tipo=o.id_tipo ORDER BY o.id_ocorrencia DESC LIMIT 6")->fetchAll();
?>
<section class="admin-stats">
  <article class="admin-stat"><span class="label">Usuários</span><div class="value"><?= $totalUsuarios ?></div><span class="hint">contas cadastradas</span></article>
  <article class="admin-stat"><span class="label">Ocorrências</span><div class="value"><?= $totalOcorrencias ?></div><span class="hint"><?= $pendentes ?> pendentes</span></article>
  <article class="admin-stat"><span class="label">Rotas</span><div class="value"><?= $totalRotas ?></div><span class="hint">consultas registradas</span></article>
</section>
<section class="admin-panel">
  <h2>Registros recentes</h2><p class="panel-subtitle">Últimas ocorrências cadastradas no sistema.</p>
  <div style="overflow:auto"><table class="admin-table"><thead><tr><th>ID</th><th>Tipo</th><th>Descrição</th><th>Data</th><th>Status</th></tr></thead><tbody>
  <?php if (!$ultimos): ?><tr><td colspan="5" class="admin-empty">Nenhum registro encontrado.</td></tr>
  <?php else: foreach($ultimos as $r): ?><tr><td>#<?= (int)$r['id_ocorrencia'] ?></td><td><?= htmlspecialchars($r['nome_tipo']) ?></td><td><?= htmlspecialchars(mb_strimwidth($r['descricao']??'',0,70,'...')) ?></td><td><?= htmlspecialchars($r['data']) ?> <?= htmlspecialchars($r['horario']) ?></td><td><span class="admin-badge status-<?= htmlspecialchars($r['status']) ?>"><?= htmlspecialchars(ucfirst($r['status'])) ?></span></td></tr><?php endforeach; endif; ?>
  </tbody></table></div>
</section>

  </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
