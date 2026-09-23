<?php
/**
 * SafeRoute — Página Inicial
 * 
 * Landing page com apresentação do sistema.
 * Conforme SPEC.md Seção 10 — Página inicial:
 * - Nome
 * - Objetivo
 * - Botões principais
 * - Resumo do funcionamento
 */

$page_title = 'SafeRoute — Segurança inteligente para seus caminhos';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main>
    <!-- ========== Hero Section ========== -->
    <section class="hero">
        <div class="container">
            <h1>🛡️ SafeRoute</h1>
            <p class="hero-slogan">Segurança inteligente para seus caminhos.</p>
            <p class="hero-description">
                Plataforma digital de segurança urbana que utiliza geolocalização e 
                dados de ocorrências para apresentar informações de risco estimado 
                em determinadas regiões, auxiliando seus deslocamentos diários.
            </p>
            <div class="hero-buttons">
                <a href="<?= BASE_URL ?>pages/mapa.php" class="btn btn-primary btn-lg">
                    🗺️ Ver Mapa
                </a>
                <a href="<?= BASE_URL ?>pages/ocorrencias.php" class="btn btn-secondary btn-lg">
                    📋 Registrar Ocorrência
                </a>
            </div>
        </div>
    </section>

    <!-- ========== Funcionalidades ========== -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Funcionalidades</h2>
            <p class="section-subtitle">
                Ferramentas para auxiliar seus deslocamentos com informações de segurança.
            </p>

            <div class="features-grid">
                <div class="card">
                    <div class="card-icon">🗺️</div>
                    <h3>Mapa Interativo</h3>
                    <p>
                        Visualize ocorrências distribuídas geograficamente com 
                        marcadores, filtros e níveis de risco por região.
                    </p>
                </div>
                <div class="card">
                    <div class="card-icon">📋</div>
                    <h3>Registro de Ocorrências</h3>
                    <p>
                        Registre e consulte ocorrências de segurança com tipo, 
                        localização, data e horário.
                    </p>
                </div>
                <div class="card">
                    <div class="card-icon">🛤️</div>
                    <h3>Planejamento de Rotas</h3>
                    <p>
                        Informe origem e destino para comparar trajetos considerando 
                        dados de segurança disponíveis.
                    </p>
                </div>
                <div class="card">
                    <div class="card-icon">📊</div>
                    <h3>Análise de Risco</h3>
                    <p>
                        Consulte estimativas de risco baseadas em quantidade, tipo, 
                        frequência e recência das ocorrências.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== Como Funciona ========== -->
    <section class="section" style="background: var(--bg-white);">
        <div class="container">
            <h2 class="section-title">Como Funciona</h2>
            <p class="section-subtitle">
                Três passos simples para utilizar o SafeRoute.
            </p>

            <div class="steps">
                <div class="step">
                    <span class="step-number">1</span>
                    <h3>Consulte o Mapa</h3>
                    <p>
                        Acesse o mapa interativo e visualize as ocorrências 
                        registradas na região desejada.
                    </p>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <h3>Planeje sua Rota</h3>
                    <p>
                        Informe origem e destino para ver opções de trajetos 
                        com análise de risco estimado.
                    </p>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <h3>Contribua</h3>
                    <p>
                        Registre ocorrências para ajudar a manter as informações 
                        atualizadas para todos os usuários.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== Aviso de Demonstração ========== -->
    <section class="section">
        <div class="container">
            <div class="demo-notice">
                ⚠️ <strong>DADOS DE DEMONSTRAÇÃO</strong> — Este sistema utiliza 
                dados fictícios para fins educacionais. As informações apresentadas 
                não representam ocorrências reais.
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
