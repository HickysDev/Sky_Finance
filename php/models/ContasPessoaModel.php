<?php

include_once __DIR__ . '/../../conn/conn.php';

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
            WHERE cp.responsavel_id = :rid AND cp.usuario_id = 1
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
            VALUES (1, :rid, :desc, :valor, :data, :cat, :metodo)
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
        $stmt = $conn->prepare("UPDATE contas_pessoa SET pago = :pago WHERE id = :id AND usuario_id = 1");
        return $stmt->execute([':pago' => (int) $pago, ':id' => $id]);
    }

    public static function remover(int $id): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("DELETE FROM contas_pessoa WHERE id = :id AND usuario_id = 1");
        return $stmt->execute([':id' => $id]);
    }

    public static function resumo(int $mes, int $ano): array {
        $conn = Database::getConnection();

        // Busca responsáveis + totais de contas_pessoa (eu devo)
        $stmt = $conn->prepare("
            SELECT
                r.id,
                r.nome,
                r.cor,
                COALESCE(SUM(CASE WHEN cp.pago = 0 THEN cp.valor ELSE 0 END), 0) AS eu_devo,
                COALESCE(SUM(CASE WHEN cp.pago = 1 THEN cp.valor ELSE 0 END), 0) AS eu_paguei,
                COUNT(CASE WHEN cp.pago = 0 THEN 1 END)                           AS qtd_aberto
            FROM responsaveis r
            LEFT JOIN contas_pessoa cp ON cp.responsavel_id = r.id AND cp.usuario_id = 1
            WHERE r.usuario_id = 1
            GROUP BY r.id, r.nome, r.cor
            ORDER BY eu_devo DESC, r.nome
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Busca totais "ela me deve" separadamente para cada responsável
        $stmtGastos = $conn->prepare("
            SELECT COALESCE(SUM(g.valor), 0) AS total
            FROM gastos g
            WHERE g.responsavel_id = :rid AND g.usuario_id = 1
              AND MONTH(g.data_gasto) = :mes AND YEAR(g.data_gasto) = :ano
        ");
        $stmtRec = $conn->prepare("
            SELECT COALESCE(SUM(grl.valor), 0) AS total
            FROM gastos_recorrentes gr
            JOIN gastos_recorrentes_lancamentos grl ON grl.gasto_recorrente_id = gr.id
            WHERE gr.responsavel_id = :rid AND gr.usuario_id = 1
              AND MONTH(grl.mes_referencia) = :mes AND YEAR(grl.mes_referencia) = :ano
        ");

        foreach ($rows as &$r) {
            $stmtGastos->execute([':rid' => $r['id'], ':mes' => $mes, ':ano' => $ano]);
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
        $conn = Database::getConnection();

        $stmt = $conn->prepare("
            SELECT g.id, g.descricao AS nome, g.valor, g.data_gasto AS data,
                   g.metodo_pagamento AS metodo, cc.nome_cartao,
                   cat.nome AS categoria, cat.cor AS cat_cor, 'avulso' AS origem
            FROM gastos g
            LEFT JOIN cartoes_credito cc  ON cc.id  = g.cartao_id
            LEFT JOIN categorias cat ON cat.id = g.categoria_id
            WHERE g.responsavel_id = :rid AND g.usuario_id = 1
              AND MONTH(g.data_gasto) = :mes AND YEAR(g.data_gasto) = :ano
        ");
        $stmt->execute([':rid' => $responsavelId, ':mes' => $mes, ':ano' => $ano]);
        $avulsas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt2 = $conn->prepare("
            SELECT grl.id, gr.nome, grl.valor, grl.mes_referencia AS data,
                   'Recorrente' AS metodo, cc.nome_cartao,
                   cat.nome AS categoria, cat.cor AS cat_cor, 'recorrente' AS origem
            FROM gastos_recorrentes gr
            JOIN gastos_recorrentes_lancamentos grl ON grl.gasto_recorrente_id = gr.id
            LEFT JOIN cartoes_credito cc  ON cc.id  = gr.cartao_id
            LEFT JOIN categorias cat ON cat.id = gr.categoria_id
            WHERE gr.responsavel_id = :rid AND gr.usuario_id = 1
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
