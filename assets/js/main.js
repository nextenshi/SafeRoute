/**
 * SafeRoute — JavaScript Principal
 * 
 * Funcionalidades globais de interface:
 * - Controle de menu responsivo (navbar toggle)
 * - Utilitários de interface compartilhados
 */

document.addEventListener('DOMContentLoaded', () => {
    // Menu mobile toggle
    const navbarToggle = document.getElementById('navbarToggle');
    const navbarNav = document.getElementById('navbarNav');

    if (navbarToggle && navbarNav) {
        navbarToggle.addEventListener('click', () => {
            navbarNav.classList.toggle('active');
        });
    }
});
