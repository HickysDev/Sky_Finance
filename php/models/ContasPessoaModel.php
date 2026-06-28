<?php

include_once __DIR__ . '/../../conn/conn.php';
include_once __DIR__ . '/ConfigModel.php';

class ContasPessoaModel {

    public static function listar(int $responsavelId, ?int $mes = null, ?int $ano = null): array {
        $conn = Database::getConnection();
        $filtroMes = ($mes && $ano)
            ? "AND MONTH(cp.data) = :mes AND YEAR(cp.data) = :ano"
            : "";
        $stmt = $conn->prepare("
            SELECT cp.*, cat.nome AS categoria, cat.cor AS cat_cor, cat.icone AS cat_icone
            FROM contas_pessoa cp
            LEFT JOIN categorias cat ON cat.id = cp.categoria_id
            WHERE cp.responsavel_id = :rid AND cp.usuario_id = @uid
            $filtroMes
            ORDER BY cp.pago ASC, cp.data DESC
        ");
        $params = [':rid' => $responsavelId];
        if ($mes && $ano) { $params[':mes'] = $mes; $params[':ano'] = $ano; }
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['valor'] = (float) $r['valor'];
        }
        return $rows;
    }

    public static function adicionar(int $responsavelId, string $descricao, float $valor, string $data, ?int $categoriaId = null, string $metodo = 'Dinheiro'): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            INSERT INTO contas_pessoa (usuario_id, responsavel_id, descricao, valor, data, categoria_id, metodo_pagamento)
            VALUES (@uid, :rid, :desc, :valor, :data, :cat, :metodo)
        ");
        return $stmt->execute([
            ':rid'    => $responsavelId,
            ':desc'   => trim($descricao),
            ':valor'  => $valor,
            ':data'   => $data,
            ':cat'    => $categoriaId,
            ':metodo' => $metodo,
        ]);
    }

    public static function marcarPago(int $id, bool $pago): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE contas_pessoa SET pago = :pago WHERE id = :id AND usuario_id = @uid");
        return $stmt->execute([':pago' => $pago ? 'S' : 'N', ':id' => $id]);
    }

    public static function remover(int $id): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("DELETE FROM contas_pessoa WHERE id = :id AND usuario_id = @uid");
        return $stmt->execute([':id' => $id]);
    }

    public static function resumo(int $mes, int $ano): array {
        if (ConfigModel::antesDoMarco($mes, $ano)) return [];
        $conn = Database::getConnection();

        // Busca responsáveis + totais de contas_pessoa (eu devo) do mês selecionado.
        // Filtro de data no ON do LEFT JOIN para não perder responsáveis sem conta no mês.
        $stmt = $conn->prepare("
            SELECT
                r.id,
                r.nome,
                r.cor,
                COALESCE(SUM(CASE WHEN cp.pago = 'N' THEN cp.valor ELSE 0 END), 0) AS eu_devo,
                COALESCE(SUM(CASE WHEN cp.pago = 'S' THEN cp.valor ELSE 0 END), 0) AS eu_paguei,
                COUNT(CASE WHEN cp.pago = 'N' THEN 1 END)                           AS qtd_aberto
            FROM responsaveis r
            LEFT JOIN contas_pessoa cp
                ON cp.responsavel_id = r.id AND cp.usuario_id = @uid
                AND MONTH(cp.data) = :mes AND YEAR(cp.data) = :ano
            WHERE r.usuario_id = @uid
            GROUP BY r.id, r.nome, r.cor
            ORDER BY eu_devo DESC, r.nome
        ");
        $stmt->execute([':mes' => $mes, ':ano' => $ano]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Busca totais "ela me deve" separadamente para cada responsável.
        // Crédito usa data de vencimento (igual ao restante do sistema); demais usam data_gasto.
        $stmtGastos = $conn->prepare("
            SELECT COALESCE(SUM(v), 0) AS total FROM (
                SELECT g.valor AS v
                FROM gastos g
                WHERE g.responsavel_id = :rid1 AND g.usuario_id = @uid
                  AND g.metodo_pagamento != 'Crédito'
                  AND MONTH(g.data_gasto) = :mes1 AND YEAR(g.data_gasto) = :ano1

                UNION ALL

                SELECT g.valor AS v
                FROM gastos g
                WHERE g.responsavel_id = :rid2 AND g.usuario_id = @uid
                  AND g.metodo_pagamento = 'Crédito' AND g.parcelado = 'N'
                  AND MONTH(g.dataVencimento) = :mes2 AND YEAR(g.dataVencimento) = :ano2

                UNION ALL

                SELECT p.valor_parcela AS v
                FROM gastos g
                INNER JOIN parcelas p ON p.gasto_id = g.id
                WHERE g.responsavel_id = :rid3 AND g.usuario_id = @uid
                  AND g.metodo_pagamento = 'Crédito' AND g.parcelado = 'S'
                  AND MONTH(p.data_vencimento) = :mes3 AND YEAR(p.data_vencimento) = :ano3
            ) t
        ");
        $stmtRec = $conn->prepare("
            SELECT COALESCE(SUM(grl.valor), 0) AS total
            FROM gastos_recorrentes gr
            JOIN gastos_recorrentes_lancamentos grl ON grl.gasto_recorrente_id = gr.id
            WHERE gr.responsavel_id = :rid AND gr.usuario_id = @uid
              AND MONTH(grl.mes_referencia) = :mes AND YEAR(grl.mes_referencia) = :ano
        ");

        foreach ($rows as &$r) {
            $stmtGastos->execute([
                ':rid1' => $r['id'], ':mes1' => $mes, ':ano1' => $ano,
                ':rid2' => $r['id'], ':mes2' => $mes, ':ano2' => $ano,
                ':rid3' => $r['id'], ':mes3' => $mes, ':ano3' => $ano,
            ]);
            $totalGastos = (float) $stmtGastos->fetchColumn();

            $stmtRec->execute([':rid' => $r['id'], ':mes' => $mes, ':ano' => $ano]);
            $totalRec = (float) $stmtRec->fetchColumn();

            $r['eu_devo']    = (float) $r['eu_devo'];
            $r['eu_paguei']  = (float) $r['eu_paguei'];
            $r['qtd_aberto'] = (int)   $r['qtd_aberto'];
            $r['me_deve']    = $totalGastos + $totalRec;
        }

        return $rows;
    }

    public static function despesasMeDeve(int $responsavelId, int $mes, int $ano): array {
        if (ConfigModel::antesDoMarco($mes, $ano)) return [];
        $conn = Database::getConnection();

        // Não-crédito: filtra por data_gasto
        $stmt = $conn->prepare("
            SELECT g.id, g.descricao AS nome, g.valor, g.data_gasto AS data,
                   g.metodo_pagamento AS metodo, cc.nome_cartao,
                   cat.nome AS categoria, cat.cor AS cat_cor, 'avulso' AS origem
            FROM gastos g
            LEFT JOIN cartoes_credito cc  ON cc.id  = g.cartao_id
            LEFT JOIN categorias cat ON cat.id = g.categoria_id
            WHERE g.responsavel_id = :rid AND g.usuario_id = @uid
              AND g.metodo_pagamento != 'Crédito'
              AND MONTH(g.data_gasto) = :mes AND YEAR(g.data_gasto) = :ano
        ");
        $stmt->execute([':rid' => $responsavelId, ':mes' => $mes, ':ano' => $ano]);
        $avulsas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Crédito não parcelado: filtra por dataVencimento
        $stmtCred = $conn->prepare("
            SELECT g.id, g.descricao AS nome, g.valor, g.dataVencimento AS data,
                   g.metodo_pagamento AS metodo, cc.nome_cartao,
                   cat.nome AS categoria, cat.cor AS cat_cor, 'avulso' AS origem
            FROM gastos g
            LEFT JOIN cartoes_credito cc  ON cc.id  = g.cartao_id
            LEFT JOIN categorias cat ON cat.id = g.categoria_id
            WHERE g.responsavel_id = :rid AND g.usuario_id = @uid
              AND g.metodo_pagamento = 'Crédito' AND g.parcelado = 'N'
              AND MONTH(g.dataVencimento) = :mes AND YEAR(g.dataVencimento) = :ano
        ");
        $stmtCred->execute([':rid' => $responsavelId, ':mes' => $mes, ':ano' => $ano]);
        $avulsas = array_merge($avulsas, $stmtCred->fetchAll(PDO::FETCH_ASSOC));

        // Crédito parcelado: filtra por data_vencimento da parcela
        $stmtParc = $conn->prepare("
            SELECT g.id, g.descricao AS nome, p.valor_parcela AS valor, p.data_vencimento AS data,
                   g.metodo_pagamento AS metodo, cc.nome_cartao,
                   cat.nome AS categoria, cat.cor AS cat_cor, 'avulso' AS origem
            FROM gastos g
            INNER JOIN parcelas p ON p.gasto_id = g.id
            LEFT JOIN cartoes_credito cc  ON cc.id  = g.cartao_id
            LEFT JOIN categorias cat ON cat.id = g.categoria_id
            WHERE g.responsavel_id = :rid AND g.usuario_id = @uid
              AND g.metodo_pagamento = 'Crédito' AND g.parcelado = 'S'
              AND MONTH(p.data_vencimento) = :mes AND YEAR(p.data_vencimento) = :ano
        ");
        $stmtParc->execute([':rid' => $responsavelId, ':mes' => $mes, ':ano' => $ano]);
        $avulsas = array_merge($avulsas, $stmtParc->fetchAll(PDO::FETCH_ASSOC));

        $stmt2 = $conn->prepare("
            SELECT grl.id, gr.nome, grl.valor, grl.mes_referencia AS data,
                   'Recorrente' AS metodo, cc.nome_cartao,
                   cat.nome AS categoria, cat.cor AS cat_cor, 'recorrente' AS origem
            FROM gastos_recorrentes gr
            JOIN gastos_recorrentes_lancamentos grl ON grl.gasto_recorrente_id = gr.id
            LEFT JOIN cartoes_credito cc  ON cc.id  = gr.cartao_id
            LEFT JOIN categorias cat ON cat.id = gr.categoria_id
            WHERE gr.responsavel_id = :rid AND gr.usuario_id = @uid
              AND MONTH(grl.mes_referencia) = :mes AND YEAR(grl.mes_referencia) = :ano
        ");
        $stmt2->execute([':rid' => $responsavelId, ':mes' => $mes, ':ano' => $ano]);
        $recorrentes = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $todos = array_merge($avulsas, $recorrentes);
        usort($todos, function($a, $b) { return strcmp($b['data'], $a['data']); });
        foreach ($todos as &$d) { $d['valor'] = (float) $d['valor']; }
        return $todos;
    }
}
