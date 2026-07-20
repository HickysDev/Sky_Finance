<?php

include_once __DIR__ . '/../../conn/conn.php';
include_once __DIR__ . '/ConfigModel.php';

/**
 * Lista de desejos: produtos que o usuário quer comprar, com link, imagem e valor.
 * Todas as queries filtram por usuario_id = @uid — cada usuário só vê e mexe no que é seu.
 */
class ListaDesejosModel {

    private static $prioridades = ['alta', 'media', 'baixa'];

    private static function parseValor(string $raw): float {
        $clean = str_replace(['R$', ' ', '.'], '', $raw);
        return (float) str_replace(',', '.', $clean);
    }

    private static function normalizaPrioridade(?string $p): string {
        $p = strtolower(trim((string) $p));
        return in_array($p, self::$prioridades, true) ? $p : 'media';
    }

    public static function listar(): array {
        $conn = Database::getConnection();
        // Não comprados primeiro; entre eles, prioridade alta antes; depois mais recentes.
        $stmt = $conn->prepare("
            SELECT id, nome, link, imagem, valor, prioridade, comprado, created_at
            FROM lista_desejos
            WHERE usuario_id = @uid
            ORDER BY comprado ASC,
                     FIELD(prioridade, 'alta', 'media', 'baixa'),
                     created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cria ou edita (quando $id > 0). Retorna o id do item, ou 0 em falha.
     * $imagem já vem resolvida pelo controller (URL og:image, URL colada, ou arquivo local).
     */
    public static function salvar(array $data, int $id = 0): int {
        $conn  = Database::getConnection();
        $nome  = trim($data['nome'] ?? '');
        if ($nome === '') return 0;

        $link       = trim($data['link'] ?? '') ?: null;
        $imagem     = trim($data['imagem'] ?? '') ?: null;
        $valor      = self::parseValor($data['valor'] ?? '0');
        $prioridade = self::normalizaPrioridade($data['prioridade'] ?? 'media');

        if ($id > 0) {
            // COALESCE na imagem: se o form não enviar imagem nova, mantém a atual.
            $stmt = $conn->prepare("
                UPDATE lista_desejos
                SET nome = :nome, link = :link,
                    imagem = COALESCE(:imagem, imagem),
                    valor = :valor, prioridade = :prioridade
                WHERE id = :id AND usuario_id = @uid
            ");
            $stmt->execute([
                ':nome' => $nome, ':link' => $link, ':imagem' => $imagem,
                ':valor' => $valor, ':prioridade' => $prioridade, ':id' => $id,
            ]);
            return $id;
        }

        $stmt = $conn->prepare("
            INSERT INTO lista_desejos (usuario_id, nome, link, imagem, valor, prioridade)
            VALUES (@uid, :nome, :link, :imagem, :valor, :prioridade)
        ");
        $stmt->execute([
            ':nome' => $nome, ':link' => $link, ':imagem' => $imagem,
            ':valor' => $valor, ':prioridade' => $prioridade,
        ]);
        return (int) $conn->lastInsertId();
    }

    /** Retorna o nome do arquivo de imagem local do item, se houver (para apagar no disco). */
    public static function imagemLocalDe(int $id): ?string {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT imagem FROM lista_desejos WHERE id = ? AND usuario_id = @uid");
        $stmt->execute([$id]);
        $img = $stmt->fetchColumn();
        // Local = não começa com http (URLs externas não são apagadas).
        return ($img && stripos($img, 'http') !== 0) ? $img : null;
    }

    public static function remover(int $id): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("DELETE FROM lista_desejos WHERE id = :id AND usuario_id = @uid");
        return $stmt->execute([':id' => $id]);
    }

    public static function toggleComprado(int $id): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            UPDATE lista_desejos SET comprado = IF(comprado = 'S', 'N', 'S')
            WHERE id = :id AND usuario_id = @uid
        ");
        return $stmt->execute([':id' => $id]);
    }

    /** Confirma que o item é do usuário logado (usado antes de salvar imagem enviada). */
    public static function pertenceAoUsuario(int $id): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT 1 FROM lista_desejos WHERE id = ? AND usuario_id = @uid");
        $stmt->execute([$id]);
        return (bool) $stmt->fetchColumn();
    }

    /** Grava a imagem de um item já existente (usado pelo upload após o insert). */
    public static function definirImagem(int $id, string $imagem): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE lista_desejos SET imagem = ? WHERE id = ? AND usuario_id = @uid");
        return $stmt->execute([$imagem, $id]);
    }

    /** Totais para o cabeçalho: quantos itens e a soma do que ainda falta comprar. */
    public static function resumo(): array {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT
                COUNT(*)                                              AS total,
                COALESCE(SUM(CASE WHEN comprado = 'N' THEN 1 ELSE 0 END), 0)     AS pendentes,
                COALESCE(SUM(CASE WHEN comprado = 'N' THEN valor ELSE 0 END), 0) AS total_pendente,
                COALESCE(SUM(CASE WHEN comprado = 'S' THEN 1 ELSE 0 END), 0)     AS comprados
            FROM lista_desejos
            WHERE usuario_id = @uid
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
