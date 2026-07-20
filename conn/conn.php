<?php

include_once __DIR__ . '/../php/services/BackupAutomatico.php';

// Credenciais em um lugar só — usadas pela conexão e pelo backup automático.
define('DB_HOST', 'localhost');
define('DB_NAME', 'projeto');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * PDOStatement instrumentado: avisa o backup automático quando a query
 * executada alterou dados. Como todo model usa prepare()->execute(), este é o
 * único ponto necessário para cobrir o sistema inteiro.
 */
class StatementComBackup extends PDOStatement
{
    protected function __construct() {}

    public function execute(?array $params = null): bool
    {
        $ok = parent::execute($params);
        if ($ok && BackupAutomatico::ehEscrita($this->queryString)) {
            BackupAutomatico::marcarAlteracao();
        }
        return $ok;
    }
}

/**
 * PDO instrumentado: cobre também o exec() direto, usado na restauração de
 * backup e na limpeza de dados.
 */
class ConexaoComBackup extends PDO
{
    public function exec(string $statement): int|false
    {
        $r = parent::exec($statement);
        if ($r !== false && BackupAutomatico::ehEscrita($statement)) {
            BackupAutomatico::marcarAlteracao();
        }
        return $r;
    }
}

class Database {
    private static $conn;

    public static function getConnection() {
        if (!isset(self::$conn)) {
            try {
                self::$conn = new ConexaoComBackup(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS
                );
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$conn->setAttribute(PDO::ATTR_STATEMENT_CLASS, [StatementComBackup::class, []]);
                // Vincula esta conexão ao usuário logado: as queries usam `usuario_id = @uid`.
                self::$conn->exec("SET @uid = " . self::usuarioLogadoId());
            } catch (PDOException $e) {
                die("Erro na conexão: " . $e->getMessage());
            }
        }
        return self::$conn;
    }

    /**
     * ID do usuário logado a partir da sessão (0 se não houver).
     * Garante que a sessão esteja iniciada antes de ler.
     */
    public static function usuarioLogadoId(): int {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Strict');
            session_start();
        }
        return (int) ($_SESSION['usuario_id'] ?? 0);
    }
}
