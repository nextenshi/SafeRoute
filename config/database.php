<?php
/**
 * SafeRoute: configura o banco sem guardar senhas no GitHub.
 * Le .env diretamente, inclusive quando putenv() nao existe na hospedagem.
 */
function safeRouteEnvConfig(): array {
    static $values = null;
    if ($values !== null) return $values;
    $values = [];
    $path = dirname(__DIR__) . '/.env';
    if (!is_file($path) || !is_readable($path)) return $values;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $name)) continue;
        $value = trim($value);
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = substr($value, -1);
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }
        $values[$name] = $value;
    }
    return $values;
}
function safeRouteConfig(string $name, string $default = ''): string {
    $environment = getenv($name);
    if ($environment !== false) return (string) $environment;
    $settings = safeRouteEnvConfig();
    return array_key_exists($name, $settings) ? $settings[$name] : $default;
}

/* Descobre o endereco do projeto em XAMPP (/SafeRoute/) ou na raiz da hospedagem (/). */
$projectRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
$detectedBase = '/SafeRoute/';
if ($documentRoot !== '') {
    $prefix = rtrim($documentRoot, DIRECTORY_SEPARATOR);
    if ($projectRoot === $prefix) {
        $detectedBase = '/';
    } elseif (str_starts_with($projectRoot, $prefix . DIRECTORY_SEPARATOR)) {
        $relative = substr($projectRoot, strlen($prefix) + 1);
        $detectedBase = '/' . trim(str_replace('\\', '/', $relative), '/') . '/';
    }
}
$base = safeRouteConfig('APP_BASE_PATH', $detectedBase);
if (!preg_match('#^/[a-zA-Z0-9_/-]*$#', $base)) $base = $detectedBase;
// Se index.php reside na raiz publica, nao deve apontar para /SafeRoute/.
if ($documentRoot !== '' && $projectRoot === rtrim($documentRoot, DIRECTORY_SEPARATOR)) {
    $base = '/';
}
define('BASE_URL', rtrim($base, '/') . '/');

define('DB_HOST', safeRouteConfig('DB_HOST'));
define('DB_PORT', safeRouteConfig('DB_PORT', '3306'));
define('DB_NAME', safeRouteConfig('DB_NAME'));
define('DB_USER', safeRouteConfig('DB_USER'));
define('DB_PASS', safeRouteConfig('DB_PASS'));
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
