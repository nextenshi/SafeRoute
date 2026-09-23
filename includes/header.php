<?php
/**
 * SafeRoute — Cabeçalho HTML
 * 
 * Incluído no topo de todas as páginas.
 * 
 * Variáveis opcionais (definir ANTES de incluir este arquivo):
 *   $page_title : string — Título da página
 *   $extra_css  : array  — URLs de CSS adicionais (ex: Leaflet)
 */

// Valores padrão
$page_title = $page_title ?? 'SafeRoute — Segurança inteligente para seus caminhos';
$extra_css  = $extra_css ?? [];

// Garantir que BASE_URL está definida
if (!defined('BASE_URL')) {
    define('BASE_URL', '/SafeRoute/');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SafeRoute — Sistema Inteligente de Segurança Urbana e Planejamento de Rotas">
    <title><?= htmlspecialchars($page_title) ?></title>

    <!-- CSS Principal -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <!-- CSS adicional (definido por cada página) -->
    <?php foreach ($extra_css as $css): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
    <?php endforeach; ?>
</head>
<body>
