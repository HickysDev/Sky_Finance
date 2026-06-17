<?php

include_once __DIR__ . '/../../conn/conn.php';

class ResponsaveisModel {

    public static function buscar(): array {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT * FROM responsaveis WHERE usuario_id = 1 ORDER BY nome");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function adicionar(string $nome, string $cor): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("INSERT INTO responsaveis (usuario_id, nome, cor) VALUES (1, :nome, :cor)");
        return $stmt->execute([':nome' => trim($nome), ':cor' => $cor]);
    }

    public static function editar(int $id, string $nome, string $cor): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE responsaveis SET nome = :nome, cor = :cor WHERE id = :id AND usuario_id = 1");
        return $stmt->execute([':nome' => trim($nome), ':cor' => $cor, ':id' => $id]);
    }

    public static function excluir(int $id): bool {
        $conn = Database::getConnection();
        // Remove vínculo das despesas antes de excluir
        $conn->prepare("UPDATE gastos SET responsavel_id = NULL WHERE responsavel_id = :id")
             ->execute([':id' => $id]);
        $conn->prepare("UPDATE gastos_recorrentes SET responsavel_id = NULL WHERE responsavel_id = :id")
             ->execute([':id' => $id]);
        $stmt = $conn->prepare("DELETE FROM responsaveis WHERE id = :id AND usuario_id = 1");
        return $stmt->execute([':id' => $id]);
    }

    public static function resumo(int $mes, int $ano): array {
        $conn = Database::getConnection();

        $stmt = $conn->prepare("
            SELECT
                r.id,
                r.nome,
                r.cor,
                (
                    SELECT COALESCE(SUM(g.valor), 0)
                    FROM gastos g
                    WHERE g.responsavel_id = r.id
                      AND g.usuario_id = 1
                      AND MONTH(g.data_gasto) = :mes
                      AND YEAR(g.data_gasto)  = :ano
                ) +
                (
                    SELECT COALESCE(SUM(grl.valor), 0)
                    FROM gastos_recorrentes gr
                    JOIN gastos_recorrentes_lancamentos grl ON grl.gasto_recorrente_id = gr.id
                    WHERE gr.responsavel_id = r.id
                      AND gr.usuario_id = 1
                      AND MONTH(grl.mes_referencia) = :mes2
                      AND YEAR(grl.mes_referencia)  = :ano2
                ) AS total
            FROM responsaveis r
            WHERE r.usuario_id = 1
            ORDER BY total DESC, r.nome
        ");

        $stmt->execute([':mes' => $mes, ':ano' => $ano, ':mes2' => $mes, ':ano2' => $ano]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['total'] = (float) $r['total'];
        }

        return $rows;
    }

    public static function despesas(int $responsavelId, int $mes, int $ano): array {
        $conn = Database::getConnection();

        // Despesas avulsas
        $stmt = $conn->prepare("
            SELECT g.id, g.descricao AS nome, g.valor, g.data_gasto AS data,
                   g.metodo_pagamento AS metodo, cc.nome_cartao, cat.nome AS categoria, cat.cor AS cat_cor, 'avulso' AS origem
            FROM gastos g
            LEFT JOIN cartoes_credito cc  ON cc.id  = g.cartao_id
            LEFT JOIN categorias cat ON cat.id = g.categoria_id
            WHERE g.responsavel_id = :rid
              AND g.usuario_id = 1
              AND MONTH(g.data_gasto) = :mes
              AND YEAR(g.data_gasto)  = :ano
        ");
        $stmt->execute([':rid' => $responsavelId, ':mes' => $mes, ':ano' => $ano]);
        $avulsas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lançamentos recorrentes
        $stmt2 = $conn->prepare("
            SELECT grl.id, gr.nome, grl.valor, grl.mes_referencia AS data,
                   'Crédito' AS metodo, cc.nome_cartao, cat.nome AS categoria, cat.cor AS cat_cor, 'recorrente' AS origem
            FROM gastos_recorrentes gr
            JOIN gastos_recorrentes_lancamentos grl ON grl.gasto_recorrente_id = gr.id
            LEFT JOIN cartoes_credito cc  ON cc.id  = gr.cartao_id
            LEFT JOIN categorias cat ON cat.id = gr.categoria_id
            WHERE gr.responsavel_id = :rid
              AND gr.usuario_id = 1
              AND MONTH(grl.mes_referencia) = :mes
              AND YEAR(grl.mes_referencia)  = :ano
        ");
        $stmt2->execute([':rid' => $responsavelId, ':mes' => $mes, ':ano' => $ano]);
        $recorrentes = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $todos = array_merge($avulsas, $recorrentes);
        usort($todos, fn($a, $b) => strcmp($b['data'], $a['data']));

        foreach ($todos as &$d) {
            $d['valor'] = (float) $d['valor'];
        }

        return $todos;
    }
}
