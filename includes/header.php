<?php
$page_title=$page_title ?? 'SafeRoute — Segurança inteligente para seus caminhos';
$extra_css=$extra_css ?? [];
$extra_js=$extra_js ?? [];
if(!defined('BASE_URL')) define('BASE_URL','/SafeRoute/');
?>
<!DOCTYPE html><html lang="pt-BR"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="SafeRoute — Sistema Inteligente de Segurança Urbana e Planejamento de Rotas">
<title><?= htmlspecialchars($page_title) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
<?php foreach($extra_css as $css): ?><link rel="stylesheet" href="<?= htmlspecialchars($css) ?>"><?php endforeach; ?>
<?php foreach($extra_js as $js): ?><script src="<?= htmlspecialchars($js) ?>"></script><?php endforeach; ?>
</head><body>
