<?php
// Script auxiliar para gerar hash de senha
// Execute: php gerar_hash.php
// Após usar, pode ser removido.
echo password_hash('admin123', PASSWORD_DEFAULT) . PHP_EOL;
