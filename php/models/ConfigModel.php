<?php

include_once __DIR__ . '/../../conn/conn.php';

/**
 * Configurações globais do controle financeiro.
 * Hoje guarda o "marco inicial": mês/ano a partir do qual o sistema conta os dados.
 * Tudo anterior ao marco é ignorado em todas as telas (aparece zerado).
 */
class ConfigModel {

    // Cache por request para não consultar o banco a cada chamada
    private static $mesInicioCache = false; // false = ainda não carregado

    /**
     * Retorna o marco inicial como 'YYYY-MM-01' ou null se não houver.
     */
    public static function getMesInicio(): ?string {
        if (self::$mesInicioCache !== false) {
            return self::$mesInicioCache;
        }
        try {
            $conn = Database::getConnection();
            $stmt = $conn->prepare("SELECT mes_inicio_controle FROM usuarios WHERE id = ?");
            $stmt->execute([Database::usuarioLogadoId()]);
            $val = $stmt->fetchColumn();
            self::$mesInicioCache = $val ? substr($val, 0, 7) . '-01' : null;
        } catch (Exception $e) {
            self::$mesInicioCache = null;
        }
        return self::$mesInicioCache;
    }

    /**
     * Define (ou limpa, com null) o marco inicial. Aceita 'YYYY-MM' ou 'YYYY-MM-DD'.
     */
    public static function setMesInicio(?string $anoMes): bool {
        $conn = Database::getConnection();
        $data = null;
        if ($anoMes && preg_match('/^(\d{4})-(\d{2})/', $anoMes, $m)) {
            $data = $m[1] . '-' . $m[2] . '-01';
        }
        $ok = $conn->prepare("UPDATE usuarios SET mes_inicio_controle = ? WHERE id = ?")
                   ->execute([$data, Database::usuarioLogadoId()]);
        self::$mesInicioCache = $data ? substr($data, 0, 7) . '-01' : null;
        return $ok;
    }

    /**
     * True se o mês/ano informado for ANTERIOR ao marco (deve ser ignorado).
     * Sem marco definido, nunca ignora nada.
     */
    public static function antesDoMarco($mes, $ano): bool {
        $marco = self::getMesInicio();
        if (!$marco) return false;
        $alvo  = sprintf('%04d-%02d-01', (int) $ano, (int) $mes);
        return $alvo < $marco;
    }
}
