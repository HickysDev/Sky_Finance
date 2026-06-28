<?php
class Database {
    private static $conn;

    public static function getConnection() {
        if (!isset(self::$conn)) {
            try {
                self::$conn = new PDO("mysql:host=localhost;dbname=projeto;charset=utf8mb4", "root", "");
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
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
