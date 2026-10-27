<?php
/** Testes locais das partes criptográficas, sem banco nem SMTP. Execute: php tests/recuperacao_senha.test.php */
putenv('RESET_CODE_SECRET=valor-ficticio-apenas-em-testes-com-mais-de-32-caracteres');
require_once __DIR__ . '/../includes/functions/recuperacao_senha.php';
$hashA = srResetKey(' Exemplo@Gmail.com ', '123456');
$hashB = srResetKey('exemplo@gmail.com', '123456');
$hashWrong = srResetKey('exemplo@gmail.com', '123457');
if (strlen($hashA) !== 64 || !hash_equals($hashA, $hashB) || hash_equals($hashA, $hashWrong)) {
    throw new RuntimeException('Falha na geração/normalização de hash de recuperação.');
}
if (srResetConfigured()) throw new RuntimeException('SMTP nao deveria estar configurado durante o teste.');
echo "OK - HMAC, normalização e config SMTP incompleta\n";
