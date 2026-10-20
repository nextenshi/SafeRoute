<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Admin — Usuários';
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-brand"><strong>🛡️ SafeRoute</strong><span>Área administrativa</span></div>
    <nav class="admin-menu"><a class="" href="dashboard.php">📊<span>Dashboard</span></a>
<a class="" href="registros.php">⚠️<span>Registros</span></a>
<a class="active" href="usuarios.php">👥<span>Usuários</span></a>
<a class="" href="tipos.php">🏷️<span>Tipos de ocorrência</span></a>
<a class="" href="mapa.php">🗺️<span>Mapa</span></a>
<a class="" href="estatisticas.php">📈<span>Estatísticas</span></a></nav>
  </aside>
  <main class="admin-content">
    
<?php $pdo=getConnection(); $users=$pdo->query("SELECT id_usuario,nome,email,tipo_usuario,data_cadastro FROM usuario ORDER BY id_usuario DESC")->fetchAll(); ?>
<header class="admin-top"><div><span class="eyebrow">ADMINISTRAÇÃO</span><h1>Gerenciar usuários</h1><p>Consulte as contas e os níveis de acesso.</p></div></header>
<section class="admin-panel"><h2>Usuários cadastrados</h2><p class="panel-subtitle">O administrador possui acesso às áreas de gerenciamento.</p>
<div style="overflow:auto"><table class="admin-table"><thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th><th>Cadastro</th></tr></thead><tbody>
<?php foreach($users as $u): ?><tr><td>#<?= (int)$u['id_usuario'] ?></td><td><?= htmlspecialchars($u['nome']) ?></td><td><?= htmlspecialchars($u['email']) ?></td><td><span class="admin-badge <?= $u['tipo_usuario']==='admin'?'admin':'comum' ?>"><?= htmlspecialchars($u['tipo_usuario']) ?></span></td><td><?= htmlspecialchars($u['data_cadastro']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></section>

  </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
