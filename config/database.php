<?php
/**
 * SafeRoute — Configuração do Banco de Dados
 * 
 * Conexão PDO com MySQL via XAMPP.
 * Utiliza padrão Singleton para reutilizar a conexão.
 * 
 * Configurações padrão do XAMPP:
 * - Host: localhost
 * - Usuário: root
 * - Senha: (vazio)
 */

// ============================================
// Caminho base do projeto no servidor web
// Ajuste se o projeto estiver em outra pasta
// ============================================
define('BASE_URL', '/SafeRoute/');

// ============================================
// Configurações do banco de dados
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'saferoute');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna uma conexão PDO com o banco de dados.
 * Reutiliza a mesma conexão (Singleton) para evitar múltiplas conexões.
 *
 * @return PDO Instância da conexão com o banco
 */
function getConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Erro de conexão com o banco de dados. Verifique se o XAMPP (MySQL) está ativo.');
        }
    }

    return $pdo;
}
