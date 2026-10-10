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
            SELECT d.id, d.nome, d.link, d.imagem, d.valor, d.prioridade, d.comprado, d.gasto_id, d.created_at,
                   d.categoria_id, c.nome AS categoria, c.cor AS categoria_cor, c.icone AS categoria_icone
            FROM lista_desejos d
            LEFT JOIN categorias c ON c.id = d.categoria_id AND c.usuario_id = d.usuario_id
            WHERE d.usuario_id = @uid
            ORDER BY d.comprado ASC,
                     FIELD(d.prioridade, 'alta', 'media', 'baixa'),
                     d.created_at DESC
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
        // Categoria: só aceita uma do próprio usuário; vazio = sem categoria
        $categoria  = (int) ($data['categoria_id'] ?? 0) ?: null;
        if ($categoria) {
            $c = $conn->prepare("SELECT id FROM categorias WHERE id = ? AND usuario_id = @uid");
            $c->execute([$categoria]);
            if (!$c->fetchColumn()) $categoria = null;
        }

        if ($id > 0) {
            // COALESCE na imagem: se o form não enviar imagem nova, mantém a atual.
            $stmt = $conn->prepare("
                UPDATE lista_desejos
                SET nome = :nome, link = :link,
                    imagem = COALESCE(:imagem, imagem),
                    valor = :valor, prioridade = :prioridade, categoria_id = :categoria
                WHERE id = :id AND usuario_id = @uid
            ");
            $stmt->execute([
                ':nome' => $nome, ':link' => $link, ':imagem' => $imagem,
                ':valor' => $valor, ':prioridade' => $prioridade, ':categoria' => $categoria, ':id' => $id,
            ]);
            return $id;
        }

        $stmt = $conn->prepare("
            INSERT INTO lista_desejos (usuario_id, nome, link, imagem, valor, prioridade, categoria_id)
            VALUES (@uid, :nome, :link, :imagem, :valor, :prioridade, :categoria)
        ");
        $stmt->execute([
            ':nome' => $nome, ':link' => $link, ':imagem' => $imagem,
            ':valor' => $valor, ':prioridade' => $prioridade, ':categoria' => $categoria,
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

    /** Marca como comprado guardando a despesa lançada (para poder reverter). */
    public static function registrarCompra(int $id, ?int $gastoId): bool {
        $conn = Database::getConnection();
        // Só aceita uma despesa do próprio usuário
        if ($gastoId) {
            $g = $conn->prepare("SELECT id FROM gastos WHERE id = ? AND usuario_id = @uid");
            $g->execute([$gastoId]);
            if (!$g->fetchColumn()) $gastoId = null;
        }
        $stmt = $conn->prepare("
            UPDATE lista_desejos SET comprado = 'S', gasto_id = :g
            WHERE id = :id AND usuario_id = @uid
        ");
        return $stmt->execute([':g' => $gastoId, ':id' => $id]);
    }

    /**
     * Reverte a compra: apaga a despesa lançada (e as parcelas) e devolve o item
     * para a lista. Retorna se havia despesa vinculada para apagar.
     */
    public static function reverterCompra(int $id): array {
        $conn = Database::getConnection();
        $s = $conn->prepare("SELECT gasto_id FROM lista_desejos WHERE id = ? AND usuario_id = @uid");
        $s->execute([$id]);
        $gastoId = (int) $s->fetchColumn();

        $conn->beginTransaction();
        try {
            $apagou = false;
            if ($gastoId) {
                $conn->prepare("DELETE FROM parcelas WHERE gasto_id = ? AND gasto_id IN (SELECT id FROM gastos WHERE usuario_id = @uid)")
                     ->execute([$gastoId]);
                $d = $conn->prepare("DELETE FROM gastos WHERE id = ? AND usuario_id = @uid");
                $d->execute([$gastoId]);
                $apagou = $d->rowCount() > 0;
            }
            // Compra agrupada: todos os itens ligados à mesma despesa voltam juntos
            if ($gastoId) {
                $conn->prepare("UPDATE lista_desejos SET comprado = 'N', gasto_id = NULL WHERE gasto_id = ? AND usuario_id = @uid")
                     ->execute([$gastoId]);
            }
            $conn->prepare("UPDATE lista_desejos SET comprado = 'N', gasto_id = NULL WHERE id = ? AND usuario_id = @uid")
                 ->execute([$id]);
            $conn->commit();
            return ['ok' => true, 'despesa_apagada' => $apagou];
        } catch (Exception $e) {
            $conn->rollBack();
            return ['ok' => false, 'despesa_apagada' => false];
        }
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
