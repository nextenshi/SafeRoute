<?php
function paginaDisponivel(string $arquivo): bool {
    return is_file(__DIR__ . '/../pages/' . basename($arquivo));
}
function linkPagina(string $arquivo, string $rotulo, string $classe='nav-link', string $classeSoon='nav-link-soon'): string {
    $rotuloHtml=htmlspecialchars($rotulo,ENT_QUOTES,'UTF-8');
    if (paginaDisponivel($arquivo)) {
        return '<a href="'.htmlspecialchars(BASE_URL.'pages/'.basename($arquivo),ENT_QUOTES,'UTF-8').'" class="'.$classe.'">'.$rotuloHtml.'</a>';
    }
    return '<span class="'.$classe.' '.$classeSoon.'" aria-disabled="true">'.$rotuloHtml.' <small class="soon-tag">em breve</small></span>';
}
