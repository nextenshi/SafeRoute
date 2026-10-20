<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Admin — Registros';
$pdo=getConnection();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verifyCsrf();
  $id=(int)($_POST['id_ocorrencia']??0); $acao=$_POST['acao']??'';
  if($id>0 && in_array($acao,['aprovar','rejeitar','excluir'],true)){
    if($acao==='excluir') $pdo->prepare('DELETE FROM ocorrencia WHERE id_ocorrencia=?')->execute([$id]);
    else $pdo->prepare('UPDATE ocorrencia SET status=? WHERE id_ocorrencia=?')->execute([$acao==='aprovar'?'aprovada':'rejeitada',$id]);
  }
  header('Location: registros.php'); exit;
}
$busca=trim($_GET['busca']??'');
if($busca!==''){
  $st=$pdo->prepare("SELECT o.*,t.nome_tipo,u.nome FROM ocorrencia o JOIN tipo_ocorrencia t ON t.id_tipo=o.id_tipo JOIN usuario u ON u.id_usuario=o.id_usuario WHERE o.descricao LIKE ? OR t.nome_tipo LIKE ? OR u.nome LIKE ? ORDER BY o.id_ocorrencia DESC");
  $like="%$busca%"; $st->execute([$like,$like,$like]); $rows=$st->fetchAll();
}else $rows=$pdo->query("SELECT o.*,t.nome_tipo,u.nome FROM ocorrencia o JOIN tipo_ocorrencia t ON t.id_tipo=o.id_tipo JOIN usuario u ON u.id_usuario=o.id_usuario ORDER BY o.id_ocorrencia DESC")->fetchAll();

?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-brand"><strong>🛡️ SafeRoute</strong><span>Área administrativa</span></div>
    <nav class="admin-menu"><a class="" href="dashboard.php">📊<span>Dashboard</span></a>
<a class="active" href="registros.php">⚠️<span>Registros</span></a>
<a class="" href="usuarios.php">👥<span>Usuários</span></a>
<a class="" href="tipos.php">🏷️<span>Tipos de ocorrência</span></a>
<a class="" href="mapa.php">🗺️<span>Mapa</span></a>
<a class="" href="estatisticas.php">📈<span>Estatísticas</span></a></nav>
  </aside>
  <main class="admin-content">
    

<header class="admin-top"><div><span class="eyebrow">ADMINISTRAÇÃO</span><h1>Gerenciar registros</h1><p>Consulte e modere as ocorrências cadastradas.</p></div></header>
<section class="admin-panel">
<form class="admin-search" method="get"><input name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Pesquisar por descrição, tipo ou usuário..."><button class="admin-btn primary">Pesquisar</button><?php if($busca!==''): ?><a class="admin-btn secondary" href="registros.php">Limpar</a><?php endif; ?></form>
<div style="overflow:auto"><table class="admin-table"><thead><tr><th>ID</th><th>Tipo</th><th>Usuário</th><th>Localização</th><th>Data</th><th>Status</th><th>Ação</th></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="7" class="admin-empty">Nenhum registro encontrado.</td></tr><?php else: foreach($rows as $r): ?><tr>
<td>#<?= (int)$r['id_ocorrencia'] ?></td><td><?= htmlspecialchars($r['nome_tipo']) ?></td><td><?= htmlspecialchars($r['nome']) ?></td>
<td><?= number_format((float)$r['latitude'],5,',','.') ?>, <?= number_format((float)$r['longitude'],5,',','.') ?></td>
<td><?= htmlspecialchars($r['data']) ?><br><small><?= htmlspecialchars($r['horario']) ?></small></td>
<td><span class="admin-badge status-<?= htmlspecialchars($r['status']) ?>"><?= htmlspecialchars(ucfirst($r['status'])) ?></span></td>
<td><div class="action-row"><a class="admin-btn secondary" href="editar_registro.php?id=<?= (int)$r['id_ocorrencia'] ?>">Editar</a>
<?php if($r['status']==='pendente'): ?><form method="post"><?= csrfField() ?><input type="hidden" name="id_ocorrencia" value="<?= (int)$r['id_ocorrencia'] ?>"><button name="acao" value="aprovar" class="admin-btn primary">Aprovar</button></form><form method="post"><?= csrfField() ?><input type="hidden" name="id_ocorrencia" value="<?= (int)$r['id_ocorrencia'] ?>"><button name="acao" value="rejeitar" class="admin-btn secondary">Rejeitar</button></form><?php endif; ?><form method="post" onsubmit="return confirm('Excluir este registro?')"><?= csrfField() ?><input type="hidden" name="id_ocorrencia" value="<?= (int)$r['id_ocorrencia'] ?>"><button name="acao" value="excluir" class="admin-btn danger">Excluir</button></form></div></td>
</tr><?php endforeach; endif; ?></tbody></table></div></section>

  </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
