<?php
/**
 * Configuração e conexão com o banco de dados (PDO).
 *
 * Valores padrão do XAMPP:
 *   host: localhost | usuário: root | senha: (vazia)
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'gestor_clientes');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna a conexão PDO (criada uma única vez por requisição).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // lança exceções em erros
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // retorna arrays associativos
            PDO::ATTR_EMULATE_PREPARES   => false,                  // prepared statements reais
        ]);
    } catch (PDOException $e) {
        error_log('[gestor-clientes] Falha na conexão: ' . $e->getMessage());
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        exit('<h2>Erro ao conectar ao banco de dados.</h2>'
            . '<p>Verifique se o <strong>MySQL</strong> está iniciado no XAMPP '
            . 'e se o banco <strong>gestor_clientes</strong> foi importado pelo phpMyAdmin.</p>');
    }

    return $pdo;
}
