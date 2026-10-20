<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Admin — Tipos';
$pdo=getConnection();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verifyCsrf();
 if(trim($_POST['nome_tipo']??'')===''){ http_response_code(422); exit('Informe o nome da categoria.'); }
  $st=$pdo->prepare("INSERT INTO tipo_ocorrencia(nome_tipo) VALUES(?)");
  try{$st->execute([trim($_POST['nome_tipo'])]);}catch(PDOException $e){}
  header('Location: tipos.php');exit;
}
$tipos=$pdo->query("SELECT id_tipo,nome_tipo FROM tipo_ocorrencia ORDER BY nome_tipo")->fetchAll();

?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-brand"><strong>🛡️ SafeRoute</strong><span>Área administrativa</span></div>
    <nav class="admin-menu"><a class="" href="dashboard.php">📊<span>Dashboard</span></a>
<a class="" href="registros.php">⚠️<span>Registros</span></a>
<a class="" href="usuarios.php">👥<span>Usuários</span></a>
<a class="active" href="tipos.php">🏷️<span>Tipos de ocorrência</span></a>
<a class="" href="mapa.php">🗺️<span>Mapa</span></a>
<a class="" href="estatisticas.php">📈<span>Estatísticas</span></a></nav>
  </aside>
  <main class="admin-content">
    

<header class="admin-top"><div><span class="eyebrow">ADMINISTRAÇÃO</span><h1>Tipos de ocorrência</h1><p>Organize as categorias utilizadas nos registros.</p></div></header>
<section class="admin-panel"><h2>Novo tipo</h2><p class="panel-subtitle">Adicione uma categoria para facilitar o cadastro e a filtragem.</p>
<form class="admin-search" method="post"><?= csrfField() ?><input name="nome_tipo" required maxlength="50" placeholder="Ex.: Acidente"><button class="admin-btn primary">Adicionar</button></form></section>
<section class="admin-panel"><h2>Categorias cadastradas</h2><div style="overflow:auto"><table class="admin-table"><thead><tr><th>ID</th><th>Nome</th></tr></thead><tbody><?php foreach($tipos as $t): ?><tr><td>#<?= (int)$t['id_tipo'] ?></td><td><?= htmlspecialchars($t['nome_tipo']) ?></td></tr><?php endforeach; ?></tbody></table></div></section>

  </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
