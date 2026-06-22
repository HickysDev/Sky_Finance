<?php
require_once __DIR__ . '/../../conn/conn.php';

class OrcamentoModel {

    private static function parseValor(string $raw): float {
        $clean = str_replace(['R$', ' ', '.'], '', $raw);
        return (float) str_replace(',', '.', $clean);
    }

    public static function salvar(int $categoria_id, string $valor_raw, string $meses = '', string $anos = '', int $id = 0): bool {
        $conn      = Database::getConnection();
        $valor     = self::parseValor($valor_raw);
        $meses_val = trim($meses) !== '' ? trim($meses) : null;
        $anos_val  = trim($anos)  !== '' ? trim($anos)  : null;

        if ($id > 0) {
            $stmt = $conn->prepare("
                UPDATE orcamentos
                SET valor_limite = :limite, meses = :meses, anos = :anos
                WHERE id = :id AND usuario_id = 1
            ");
            return $stmt->execute([
                ':limite' => $valor,
                ':meses'  => $meses_val,
                ':anos'   => $anos_val,
                ':id'     => $id,
            ]);
        }

        $stmt = $conn->prepare("
            INSERT INTO orcamentos (categoria_id, usuario_id, valor_limite, meses, anos)
            VALUES (:cat, 1, :limite, :meses, :anos)
        ");
        return $stmt->execute([
            ':cat'    => $categoria_id,
            ':limite' => $valor,
            ':meses'  => $meses_val,
            ':anos'   => $anos_val,
        ]);
    }

    public static function remover(int $id): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("DELETE FROM orcamentos WHERE id = :id AND usuario_id = 1");
        return $stmt->execute([':id' => $id]);
    }

    public static function buscarComGasto(int $mes, int $ano): array {
        $conn = Database::getConnection();

        $stmt = $conn->prepare("
            SELECT
                c.id        AS categoria_id,
                c.nome,
                c.cor,
                c.icone,
                o.id        AS orcamento_id,
                o.valor_limite,
                o.meses,
                o.anos,
                COALESCE(SUM(g_mes.valor_mes), 0) AS gasto_mes
            FROM orcamentos o
            INNER JOIN categorias c ON c.id = o.categoria_id AND c.ativo = 'S'
            LEFT JOIN (
                SELECT categoria_id, valor AS valor_mes
                FROM gastos
                WHERE usuario_id = 1
                  AND metodo_pagamento IN ('Dinheiro','Débito','Pix')
                  AND MONTH(data_gasto) = :m1 AND YEAR(data_gasto) = :a1

                UNION ALL

                SELECT categoria_id, valor AS valor_mes
                FROM gastos
                WHERE usuario_id = 1
                  AND metodo_pagamento = 'Crédito' AND parcelado = 'N'
                  AND MONTH(dataVencimento) = :m2 AND YEAR(dataVencimento) = :a2

                UNION ALL

                SELECT g.categoria_id, p.valor_parcela AS valor_mes
                FROM gastos g
                INNER JOIN parcelas p ON p.gasto_id = g.id
                WHERE g.usuario_id = 1
                  AND g.metodo_pagamento = 'Crédito' AND g.parcelado = 'S'
                  AND MONTH(p.data_vencimento) = :m3 AND YEAR(p.data_vencimento) = :a3

                UNION ALL

                SELECT gr.categoria_id, grl.valor AS valor_mes
                FROM gastos_recorrentes_lancamentos grl
                INNER JOIN gastos_recorrentes gr ON gr.id = grl.gasto_recorrente_id
                WHERE gr.ativo = 'S' AND gr.usuario_id = 1
                  AND (gr.mes_inicio IS NULL OR gr.mes_inicio <= grl.mes_referencia)
                  AND MONTH(grl.mes_referencia) = :m4 AND YEAR(grl.mes_referencia) = :a4
            ) g_mes ON g_mes.categoria_id = c.id
            WHERE o.usuario_id = 1
              AND (o.meses IS NULL OR FIND_IN_SET(:mes_check, o.meses) > 0)
              AND (o.anos  IS NULL OR FIND_IN_SET(:ano_check, o.anos)  > 0)
            GROUP BY c.id, c.nome, c.cor, c.icone, o.id, o.valor_limite, o.meses, o.anos
            ORDER BY (COALESCE(SUM(g_mes.valor_mes), 0) / NULLIF(o.valor_limite, 0)) DESC
        ");

        $stmt->execute([
            ':m1' => $mes, ':a1' => $ano,
            ':m2' => $mes, ':a2' => $ano,
            ':m3' => $mes, ':a3' => $ano,
            ':m4' => $mes, ':a4' => $ano,
            ':mes_check' => $mes,
            ':ano_check' => $ano,
        ]);

        return $stmt->fetchAll();
    }
}
