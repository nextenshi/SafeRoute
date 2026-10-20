<?php
/** SafeRoute: credenciais somente em .env local ou variaveis do servidor. */
function loadSafeRouteEnv(): void {
 $path = dirname(__DIR__) . '/.env';
 if (!is_file($path) || !is_readable($path)) return;
 foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
  $line = trim($line);
  if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
  [$key,$value] = explode('=', $line, 2); $key = trim($key);
  if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $key) || getenv($key) !== false) continue;
  $value = trim($value);
  if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) $value = substr($value, 1, -1);
  putenv($key . '=' . $value);
 }
}
loadSafeRouteEnv();
$base = getenv('APP_BASE_PATH') ?: '/SafeRoute/';
if (!preg_match('#^/[a-zA-Z0-9_/-]*$#', $base)) $base = '/SafeRoute/';
define('BASE_URL', rtrim($base, '/') . '/');
define('DB_HOST', getenv('DB_HOST') !== false ? getenv('DB_HOST') : '');
define('DB_PORT', getenv('DB_PORT') !== false ? getenv('DB_PORT') : '3306');
define('DB_NAME', getenv('DB_NAME') !== false ? getenv('DB_NAME') : '');
define('DB_USER', getenv('DB_USER') !== false ? getenv('DB_USER') : '');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');
function getConnection(): PDO {
 static $pdo = null;
 if ($pdo === null) {
  if (DB_HOST === '' || DB_NAME === '' || DB_USER === '' || !ctype_digit((string) DB_PORT)) {
   http_response_code(503); exit('SafeRoute nao configurado. Siga a secao Instalacao e execucao da SPEC.');
  }
  $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
  try {
   $pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
   ]);
  } catch (PDOException $e) {
   error_log('[SafeRoute] Erro PDO: ' . $e->getMessage());
   http_response_code(503); exit('Nao foi possivel conectar ao banco. Confira as variaveis de ambiente e o MySQL.');
  }
 }
 return $pdo;
}
