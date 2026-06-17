<?php

include_once __DIR__ . '/../../conn/conn.php';

class GastosModel {

    public static function buscarGastosPorMes($mes, $ano, $cartaoId, $tipo) {
        $conn = Database::getConnection();

        $params = [(int) $mes, (int) ($ano ?: date("Y"))];

        if ($tipo === 'debito') {
            $sql = "SELECT g.id, g.descricao, g.valor, c.nome, g.metodo_pagamento, g.data_gasto, g.parcelado, cc.nome_cartao
                    FROM gastos g
                    INNER JOIN categorias c ON c.id = g.categoria_id
                    LEFT JOIN cartoes_credito cc ON cc.id = g.cartao_id
                    WHERE MONTH(data_gasto) = ? AND metodo_pagamento IN ('Dinheiro', 'Débito', 'Pix') AND YEAR(data_gasto) = ?";
        } else {
            $sql = "SELECT g.id, g.descricao, g.valor, c.nome, g.metodo_pagamento, g.data_gasto, g.parcelado, cc.nome_cartao
                    FROM gastos g
                    INNER JOIN categorias c ON c.id = g.categoria_id
                    LEFT JOIN cartoes_credito cc ON cc.id = g.cartao_id
                    WHERE MONTH(dataVencimento) = ? AND metodo_pagamento = 'Crédito' AND parcelado = 'N' AND YEAR(data_gasto) = ?";
        }

        if ($cartaoId) {
            $sql .= " AND g.cartao_id = ?";
            $params[] = (int) $cartaoId;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $gastos = $stmt->fetchAll();

        $valorTotal = 0;
        foreach ($gastos as &$gasto) {
            $valorTotal += $gasto['valor'];
            $gasto['valor'] = number_format($gasto['valor'], 2, ',', '.');
        }
        $gastos['valortotal'] = number_format($valorTotal, 2, ',', '.');

        return $gastos;
    }

    public static function adicionarGasto($desc, $valor, $categoria, $pagamento = null, $cartao = null, $data = null, $parcelado = null, $num_parcelas = null, $tipo, $recorrente = null, $responsavel = null) {
        $conn = Database::getConnection();

        if ($cartao === "") {
            $cartao = null;
        }

        if ($recorrente === "S" && $tipo === "recorrente") {
            // Calcular primeiro mês de competência pela data de compra + fechamento do cartão
            $dataRef   = !empty($data) ? $data : date('Y-m-d');
            $mesInicio = date('Y-m-01', strtotime($dataRef));

            if ($cartao) {
                $buscaCartaoRec = $conn->prepare("SELECT fechamento_dia FROM cartoes_credito WHERE id = ?");
                $buscaCartaoRec->execute([(int) $cartao]);
                $cartaoRec = $buscaCartaoRec->fetch();

                if ($cartaoRec) {
                    $fechDia = (int) $cartaoRec['fechamento_dia'];
                    $diaComp = (int) date('d', strtotime($dataRef));
                    $anoMes  = date('Y-m', strtotime($dataRef));

                    if ($diaComp >= $fechDia) {
                        $mesInicio = date('Y-m-01', strtotime('+1 month', strtotime($anoMes . '-01')));
                    } else {
                        $mesInicio = $anoMes . '-01';
                    }
                }
            }

            $adicionar = $conn->prepare("
                INSERT INTO gastos_recorrentes (nome, categoria_id, cartao_id, usuario_id, valor, ativo, mes_inicio, responsavel_id)
                VALUES (:desc, :categoria, :cartao, 1, :valor, 'S', :mes_inicio, :responsavel)
            ");

            $queryAdicionar = $adicionar->execute([
                ':categoria'   => (int) $categoria,
                ':desc'        => $desc,
                ':valor'       => $valor,
                ':cartao'      => $cartao ? (int) $cartao : null,
                ':mes_inicio'  => $mesInicio,
                ':responsavel' => $responsavel,
            ]);

            if ($queryAdicionar) {
                $novoId = $conn->lastInsertId();

                $lancamento = $conn->prepare("
                    INSERT INTO gastos_recorrentes_lancamentos
                    (gasto_recorrente_id, mes_referencia, valor, nome, categoria_id, cartao_id, usuario_id)
                    VALUES (:gasto_id, :mes, :valor, :nome, :categoria, :cartao, 1)
                ");

                $lancamento->execute([
                    ':gasto_id'  => $novoId,
                    ':mes'       => $mesInicio,
                    ':valor'     => $valor,
                    ':nome'      => $desc,
                    ':categoria' => (int) $categoria,
                    ':cartao'    => $cartao ? (int) $cartao : null,
                ]);
            }

            return $queryAdicionar ? 1 : 2;
        }

        $adicionar = $conn->prepare("
            INSERT INTO gastos (usuario_id, categoria_id, descricao, valor, data_gasto, metodo_pagamento, cartao_id, parcelado, responsavel_id)
            VALUES (1, :categoria, :desc, :valor, :data, :pagamento, :cartao, :parcelado, :responsavel)
        ");

        $queryAdicionar = $adicionar->execute([
            ':categoria'   => (int) $categoria,
            ':desc'        => $desc,
            ':valor'       => $valor,
            ':data'        => $data,
            ':pagamento'   => $pagamento,
            ':cartao'      => $cartao ? (int) $cartao : null,
            ':parcelado'   => $parcelado,
            ':responsavel' => $responsavel,
        ]);

        $gastoId = $conn->lastInsertId();

        if ($tipo === 'credito' && $parcelado === "N" && $cartao) {
            $buscaCartao = $conn->prepare("SELECT fechamento_dia, vencimento_dia FROM cartoes_credito WHERE id = ?");
            $buscaCartao->execute([(int) $cartao]);
            $cartaoDados = $buscaCartao->fetch();

            if ($cartaoDados) {
                $fechamentoDia = (int) $cartaoDados['fechamento_dia'];
                $vencimentoDia = str_pad((int) $cartaoDados['vencimento_dia'], 2, '0', STR_PAD_LEFT);
                $dia    = (int) date('d', strtotime($data));
                $anoMes = date('Y-m', strtotime($data));

                // Compra antes do fechamento → vence neste mês; no dia ou depois → vence no mês seguinte
                if ($dia >= $fechamentoDia) {
                    $mesVenc = date('Y-m', strtotime('+1 month', strtotime($anoMes . '-01')));
                } else {
                    $mesVenc = $anoMes;
                }
                $dataVenc = "{$mesVenc}-{$vencimentoDia}";

                $update = $conn->prepare("UPDATE gastos SET dataVencimento = ? WHERE id = ?");
                $queryAdicionar = $update->execute([$dataVenc, $gastoId]);
            }
        }

        if ($tipo === 'credito' && $parcelado !== "N" && $num_parcelas > 0) {
            $buscaCartao = $conn->prepare("SELECT fechamento_dia, vencimento_dia FROM cartoes_credito WHERE id = ?");
            $buscaCartao->execute([(int) $cartao]);
            $cartaoDados = $buscaCartao->fetch();

            $fechamentoDia = (int) $cartaoDados['fechamento_dia'];
            $vencimentoDia = str_pad((int) $cartaoDados['vencimento_dia'], 2, '0', STR_PAD_LEFT);
            $diaCompra = (int) date('d', strtotime($data));
            $anoMes    = date('Y-m', strtotime($data));

            if ($diaCompra >= $fechamentoDia) {
                $mesVenc = date('Y-m', strtotime('+1 month', strtotime($anoMes . '-01')));
            } else {
                $mesVenc = $anoMes;
            }
            $dataVenc = "{$mesVenc}-{$vencimentoDia}";

            $valor_parcela = $valor / $num_parcelas;

            $adicionarParcelado = $conn->prepare("
                INSERT INTO parcelas (gasto_id, numero_parcela, valor_parcela, data_vencimento, parcelas_total)
                VALUES (:gastoId, :parcela, :valor_parcela, :data_vencimento, :num_parcelas)
            ");

            for ($parcela = 1; $parcela <= $num_parcelas; $parcela++) {
                $queryAdicionar = $adicionarParcelado->execute([
                    ':gastoId'         => $gastoId,
                    ':parcela'         => $parcela,
                    ':valor_parcela'   => $valor_parcela,
                    ':data_vencimento' => $dataVenc,
                    ':num_parcelas'    => $num_parcelas,
                ]);
                $dataVenc = date('Y-m-d', strtotime('+1 month', strtotime($dataVenc)));
            }
        }

        return $queryAdicionar ? 1 : 2;
    }

    public static function excluirGastos($ids, $tipo) {
        $conn = Database::getConnection();
        $queryRemover = false;

        foreach ($ids as $id) {
            if ($tipo === 'credito' && ($id['parcelado'] ?? '') === 'S') {
                $removerParcelas = $conn->prepare("DELETE FROM parcelas WHERE gasto_id = ?");
                $removerParcelas->execute([(int) $id['id']]);
            }

            $removerGasto = $conn->prepare("DELETE FROM gastos WHERE id = ?");
            $queryRemover = $removerGasto->execute([(int) $id['id']]);
        }

        return $queryRemover ? 1 : 2;
    }

    private static function gerarLancamentosParaMes($mes, $ano) {
        $conn   = Database::getConnection();
        $mesRef = sprintf('%04d-%02d-01', $ano, (int) $mes);

        $stmt = $conn->prepare("
            SELECT gr.id, gr.valor, gr.nome, gr.categoria_id, gr.cartao_id
            FROM gastos_recorrentes gr
            WHERE gr.ativo = 'S' AND gr.usuario_id = 1
              AND (gr.mes_inicio IS NULL OR gr.mes_inicio <= ?)
              AND NOT EXISTS (
                  SELECT 1 FROM gastos_recorrentes_lancamentos grl
                  WHERE grl.gasto_recorrente_id = gr.id
                    AND grl.mes_referencia = ?
              )
        ");
        $stmt->execute([$mesRef, $mesRef]);
        $pendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$pendentes) return;

        $ins = $conn->prepare("
            INSERT INTO gastos_recorrentes_lancamentos
            (gasto_recorrente_id, mes_referencia, valor, nome, categoria_id, cartao_id, usuario_id)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        foreach ($pendentes as $r) {
            $ins->execute([$r['id'], $mesRef, $r['valor'], $r['nome'], $r['categoria_id'], $r['cartao_id']]);
        }
    }

    public static function buscarFatura($mes, $ano, $cartaoId) {
        $ano_atual = (int) ($ano ?: date("Y"));
        self::gerarLancamentosParaMes($mes, $ano_atual);
        $conn = Database::getConnection();

        $condicaoGastos      = "";
        $condicaoRecorrentes = "";
        $params = [':mes' => (int) $mes, ':ano' => $ano_atual];

        if ($cartaoId) {
            $condicaoGastos      = " AND g.cartao_id = :cartao_id";
            $condicaoRecorrentes = " AND gr.cartao_id = :cartao_id";
            $params[':cartao_id'] = (int) $cartaoId;
        }

        $stmt = $conn->prepare(
            "(SELECT
                g.cartao_id,
                g.descricao,
                g.valor,
                c.nome AS categoria,
                p.numero_parcela,
                p.parcelas_total,
                p.valor_parcela,
                g.data_gasto,
                g.parcelado,
                g.id,
                cc.nome_cartao,
                'NORMAL' as tipo
            FROM gastos g
            LEFT JOIN parcelas p ON p.gasto_id = g.id
            INNER JOIN categorias c ON c.id = g.categoria_id
            INNER JOIN cartoes_credito cc ON cc.id = g.cartao_id
            WHERE g.metodo_pagamento = 'Crédito'
            {$condicaoGastos}
            AND (
                (g.parcelado = 'S' AND MONTH(p.data_vencimento) = :mes AND YEAR(p.data_vencimento) = :ano)
                OR
                (g.parcelado = 'N' AND MONTH(g.dataVencimento) = :mes AND YEAR(g.dataVencimento) = :ano)
            ))
            UNION
            (SELECT
                gr.cartao_id,
                gr.nome AS descricao,
                gr.valor,
                cat.nome AS categoria,
                NULL AS numero_parcela,
                NULL AS parcelas_total,
                NULL AS valor_parcela,
                NULL AS data_gasto,
                'N' AS parcelado,
                gr.id,
                cc.nome_cartao,
                'RECORRENTE' as tipo
            FROM gastos_recorrentes gr
            INNER JOIN gastos_recorrentes_lancamentos grl ON grl.gasto_recorrente_id = gr.id
            INNER JOIN categorias cat ON cat.id = gr.categoria_id
            LEFT JOIN cartoes_credito cc ON cc.id = gr.cartao_id
            WHERE gr.ativo = 'S'
            AND MONTH(grl.mes_referencia) = :mes
            AND YEAR(grl.mes_referencia) = :ano
            {$condicaoRecorrentes})
            ORDER BY nome_cartao, data_gasto IS NULL, data_gasto"
        );

        $stmt->execute($params);

        $faturas = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        foreach ($faturas as &$gastos) {
            $total = 0;
            foreach ($gastos as &$gasto) {
                $valor = $gasto['valor_parcela'] ?? $gasto['valor'];
                $total += $valor;
                $gasto['valor_parcela'] = number_format($valor, 2, ',', '.');
            }
            $gastos['valortotal'] = number_format($total, 2, ',', '.');
        }

        return $faturas;
    }

    public static function buscarCredito($mes, $ano, $cartaoId) {
        $conn = Database::getConnection();
        $ano_atual = (int) ($ano ?: date("Y"));

        $params = [(int) $mes, $ano_atual];

        $sql = "SELECT
                    g.descricao,
                    g.valor,
                    c.nome AS categoria,
                    p.numero_parcela,
                    p.parcelas_total,
                    p.valor_parcela,
                    g.data_gasto,
                    g.parcelado,
                    g.id,
                    cc.nome_cartao
                FROM gastos g
                INNER JOIN parcelas p ON p.gasto_id = g.id
                INNER JOIN categorias c ON c.id = g.categoria_id
                LEFT JOIN cartoes_credito cc ON cc.id = g.cartao_id
                WHERE MONTH(p.data_vencimento) = ? AND YEAR(p.data_vencimento) = ?";

        if ($cartaoId) {
            $sql .= " AND g.cartao_id = ?";
            $params[] = (int) $cartaoId;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $gastos = $stmt->fetchAll();

        $valorTotalGasto = 0;
        foreach ($gastos as &$gasto) {
            $valorTotalGasto += $gasto['valor_parcela'];
            $gasto['valor'] = number_format($gasto['valor'], 2, ',', '.');
            $gasto['valor_parcela'] = number_format($gasto['valor_parcela'], 2, ',', '.');
        }
        $gastos['valortotal'] = number_format($valorTotalGasto, 2, ',', '.');

        return $gastos;
    }

    public static function buscarRecorrentes($cartaoId = null) {
        $conn = Database::getConnection();

        $params = [];
        $sql = "SELECT
                    gr.nome,
                    gr.valor,
                    c.nome AS categoria,
                    gr.id,
                    cc.nome_cartao,
                    cc.id as id_cartao,
                    c.id as id_categoria,
                    gr.ativo,
                    gr.mes_inicio,
                    gr.inativado_em
                FROM gastos_recorrentes gr
                INNER JOIN categorias c ON c.id = gr.categoria_id
                LEFT JOIN cartoes_credito cc ON cc.id = gr.cartao_id
                WHERE gr.id IN (SELECT MAX(id) FROM gastos_recorrentes GROUP BY id)";

        if ($cartaoId) {
            $sql .= " AND gr.cartao_id = ?";
            $params[] = (int) $cartaoId;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $gastosRecorrente = $stmt->fetchAll();

        $retorno = [];
        foreach ($gastosRecorrente as $gasto) {
            $id = $gasto['id'];
            $gasto['valor'] = number_format($gasto['valor'], 2, ',', '.');
            $retorno[$id] = $gasto;
        }

        return $retorno;
    }

    public static function inativaRecorrentes($id) {
        $conn = Database::getConnection();
        $sql = $conn->prepare("UPDATE gastos_recorrentes SET ativo = 'N', inativado_em = CURDATE() WHERE id = ?");
        return $sql->execute([(int) $id]);
    }

    // Mantido para compatibilidade; preferir reativarRecorrente (com data)
    public static function ativaRecorrentes($id) {
        $conn = Database::getConnection();
        $sql = $conn->prepare("UPDATE gastos_recorrentes SET ativo = 'S', inativado_em = NULL WHERE id = ?");
        return $sql->execute([(int) $id]);
    }

    public static function reativarRecorrente($id, $data) {
        $conn = Database::getConnection();

        $stmt = $conn->prepare("SELECT cartao_id FROM gastos_recorrentes WHERE id = ?");
        $stmt->execute([(int) $id]);
        $rec = $stmt->fetch();
        if (!$rec) return false;

        $dataRef   = !empty($data) ? $data : date('Y-m-d');
        $mesInicio = date('Y-m-01', strtotime($dataRef));

        if ($rec['cartao_id']) {
            $buscaCartao = $conn->prepare("SELECT fechamento_dia FROM cartoes_credito WHERE id = ?");
            $buscaCartao->execute([(int) $rec['cartao_id']]);
            $cartaoDados = $buscaCartao->fetch();

            if ($cartaoDados) {
                $fechDia = (int) $cartaoDados['fechamento_dia'];
                $diaComp = (int) date('d', strtotime($dataRef));
                $anoMes  = date('Y-m', strtotime($dataRef));

                if ($diaComp >= $fechDia) {
                    $mesInicio = date('Y-m-01', strtotime('+1 month', strtotime($anoMes . '-01')));
                } else {
                    $mesInicio = $anoMes . '-01';
                }
            }
        }

        $upd = $conn->prepare("UPDATE gastos_recorrentes SET ativo = 'S', mes_inicio = ?, inativado_em = NULL WHERE id = ?");
        return $upd->execute([$mesInicio, (int) $id]);
    }

    public static function buscarResumoMes($mes, $ano) {
        self::gerarLancamentosParaMes($mes, $ano);
        $conn = Database::getConnection();

        // Total à vista (débito / pix / dinheiro)
        $s = $conn->prepare("
            SELECT COALESCE(SUM(valor), 0)
            FROM gastos
            WHERE MONTH(data_gasto) = ? AND YEAR(data_gasto) = ?
              AND metodo_pagamento IN ('Dinheiro','Débito','Pix')
              AND usuario_id = 1
        ");
        $s->execute([$mes, $ano]);
        $totalDebito = (float) $s->fetchColumn();

        // Total fatura crédito do mês
        $s = $conn->prepare("
            SELECT COALESCE(SUM(
                CASE WHEN g.parcelado = 'S' THEN p.valor_parcela ELSE g.valor END
            ), 0)
            FROM gastos g
            LEFT JOIN parcelas p ON p.gasto_id = g.id
            WHERE g.metodo_pagamento = 'Crédito' AND g.usuario_id = 1
              AND (
                (g.parcelado = 'S' AND MONTH(p.data_vencimento) = ? AND YEAR(p.data_vencimento) = ?)
                OR
                (g.parcelado = 'N' AND MONTH(g.dataVencimento)  = ? AND YEAR(g.dataVencimento)  = ?)
              )
        ");
        $s->execute([$mes, $ano, $mes, $ano]);
        $totalCredito = (float) $s->fetchColumn();

        // Total recorrentes ativos no mês
        $s = $conn->prepare("
            SELECT COALESCE(SUM(grl.valor), 0)
            FROM gastos_recorrentes_lancamentos grl
            INNER JOIN gastos_recorrentes gr ON gr.id = grl.gasto_recorrente_id
            WHERE gr.ativo = 'S'
              AND (gr.mes_inicio IS NULL OR gr.mes_inicio <= grl.mes_referencia)
              AND MONTH(grl.mes_referencia) = ? AND YEAR(grl.mes_referencia) = ?
        ");
        $s->execute([$mes, $ano]);
        $totalRecorrente = (float) $s->fetchColumn();

        // Gastos por categoria — todos os tipos, filtro pelo mês de competência correto
        $s = $conn->prepare("
            SELECT cat.nome, SUM(sub.valor_mes) AS total
            FROM (
                SELECT g.categoria_id, g.valor AS valor_mes
                FROM gastos g
                WHERE g.usuario_id = 1
                  AND g.metodo_pagamento IN ('Dinheiro','Débito','Pix')
                  AND MONTH(g.data_gasto) = ? AND YEAR(g.data_gasto) = ?

                UNION ALL

                SELECT g.categoria_id, g.valor AS valor_mes
                FROM gastos g
                WHERE g.usuario_id = 1
                  AND g.metodo_pagamento = 'Crédito' AND g.parcelado = 'N'
                  AND MONTH(g.dataVencimento) = ? AND YEAR(g.dataVencimento) = ?

                UNION ALL

                SELECT g.categoria_id, p.valor_parcela AS valor_mes
                FROM gastos g
                INNER JOIN parcelas p ON p.gasto_id = g.id
                WHERE g.usuario_id = 1
                  AND g.metodo_pagamento = 'Crédito' AND g.parcelado = 'S'
                  AND MONTH(p.data_vencimento) = ? AND YEAR(p.data_vencimento) = ?

                UNION ALL

                SELECT gr.categoria_id, grl.valor AS valor_mes
                FROM gastos_recorrentes_lancamentos grl
                INNER JOIN gastos_recorrentes gr ON gr.id = grl.gasto_recorrente_id
                WHERE gr.ativo = 'S'
                  AND (gr.mes_inicio IS NULL OR gr.mes_inicio <= grl.mes_referencia)
                  AND MONTH(grl.mes_referencia) = ? AND YEAR(grl.mes_referencia) = ?

                UNION ALL

                SELECT cp.categoria_id, cp.valor AS valor_mes
                FROM contas_pessoa cp
                WHERE cp.usuario_id = 1 AND cp.categoria_id IS NOT NULL
                  AND MONTH(cp.data) = ? AND YEAR(cp.data) = ?
            ) sub
            INNER JOIN categorias cat ON cat.id = sub.categoria_id
            GROUP BY sub.categoria_id, cat.nome
            ORDER BY total DESC
            LIMIT 10
        ");
        $s->execute([$mes, $ano, $mes, $ano, $mes, $ano, $mes, $ano, $mes, $ano]);
        $porCategoria = $s->fetchAll(PDO::FETCH_ASSOC);

        // Últimas 8 despesas do mês — valor da parcela para parcelados + recorrentes
        $s = $conn->prepare("
            SELECT descricao, valor, data_gasto, metodo_pagamento, categoria
            FROM (
                SELECT g.descricao, g.valor, g.data_gasto, g.metodo_pagamento,
                       cat.nome AS categoria, g.id AS gasto_id
                FROM gastos g
                INNER JOIN categorias cat ON cat.id = g.categoria_id
                WHERE g.usuario_id = 1
                  AND g.metodo_pagamento IN ('Dinheiro','Débito','Pix')
                  AND MONTH(g.data_gasto) = ? AND YEAR(g.data_gasto) = ?

                UNION ALL

                SELECT g.descricao, g.valor, g.data_gasto, g.metodo_pagamento,
                       cat.nome AS categoria, g.id AS gasto_id
                FROM gastos g
                INNER JOIN categorias cat ON cat.id = g.categoria_id
                WHERE g.usuario_id = 1
                  AND g.metodo_pagamento = 'Crédito' AND g.parcelado = 'N'
                  AND MONTH(g.dataVencimento) = ? AND YEAR(g.dataVencimento) = ?

                UNION ALL

                SELECT g.descricao, p.valor_parcela AS valor, g.data_gasto, g.metodo_pagamento,
                       cat.nome AS categoria, g.id AS gasto_id
                FROM gastos g
                INNER JOIN categorias cat ON cat.id = g.categoria_id
                INNER JOIN parcelas p ON p.gasto_id = g.id
                WHERE g.usuario_id = 1
                  AND g.metodo_pagamento = 'Crédito' AND g.parcelado = 'S'
                  AND MONTH(p.data_vencimento) = ? AND YEAR(p.data_vencimento) = ?

                UNION ALL

                SELECT gr.nome AS descricao, grl.valor, grl.mes_referencia AS data_gasto,
                       'Recorrente' AS metodo_pagamento, cat.nome AS categoria, gr.id AS gasto_id
                FROM gastos_recorrentes_lancamentos grl
                INNER JOIN gastos_recorrentes gr ON gr.id = grl.gasto_recorrente_id
                INNER JOIN categorias cat ON cat.id = gr.categoria_id
                WHERE gr.usuario_id = 1
                  AND gr.ativo = 'S'
                  AND (gr.mes_inicio IS NULL OR gr.mes_inicio <= grl.mes_referencia)
                  AND MONTH(grl.mes_referencia) = ? AND YEAR(grl.mes_referencia) = ?

                UNION ALL

                SELECT cp.descricao, cp.valor, cp.data AS data_gasto,
                       cp.metodo_pagamento, cat.nome AS categoria, cp.id AS gasto_id
                FROM contas_pessoa cp
                INNER JOIN categorias cat ON cat.id = cp.categoria_id
                WHERE cp.usuario_id = 1 AND cp.categoria_id IS NOT NULL
                  AND MONTH(cp.data) = ? AND YEAR(cp.data) = ?
            ) sub
            ORDER BY data_gasto DESC, gasto_id DESC
            LIMIT 8
        ");
        $s->execute([$mes, $ano, $mes, $ano, $mes, $ano, $mes, $ano, $mes, $ano]);
        $recentes = $s->fetchAll(PDO::FETCH_ASSOC);

        // Total renda mensal ativa (considera recorrência)
        $totalRenda = 0;
        try {
            $s = $conn->prepare("SELECT valor, recorrencia FROM renda_mensal WHERE usuario_id = 1 AND ativo = 'S'");
            $s->execute();
            $mult = ['Mensal' => 1, 'Quinzenal' => 2, 'Semanal' => 4.33, 'Anual' => 1/12, 'Único' => 0];
            foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $totalRenda += (float) $r['valor'] * ($mult[$r['recorrencia']] ?? 1);
            }
        } catch (Exception $e) {
            $totalRenda = 0;
        }

        // Total de contas a pagar a responsáveis no mês
        $totalContas = 0;
        try {
            $s = $conn->prepare("
                SELECT COALESCE(SUM(valor), 0)
                FROM contas_pessoa
                WHERE usuario_id = 1
                  AND MONTH(data) = ? AND YEAR(data) = ?
            ");
            $s->execute([$mes, $ano]);
            $totalContas = (float) $s->fetchColumn();
        } catch (Exception $e) {
            $totalContas = 0;
        }

        $totalGasto = $totalDebito + $totalCredito + $totalRecorrente + $totalContas;

        return [
            'totalDebito'      => $totalDebito,
            'totalCredito'     => $totalCredito,
            'totalRecorrente'  => $totalRecorrente,
            'totalContas'      => $totalContas,
            'totalGasto'       => $totalGasto,
            'totalRenda'       => $totalRenda,
            'saldo'            => $totalRenda - $totalGasto,
            'porCategoria'     => $porCategoria,
            'recentes'         => $recentes,
        ];
    }

    public static function resumoAnual(int $ano): array {
        $conn = Database::getConnection();

        $meses = [];
        for ($m = 1; $m <= 12; $m++) {
            $meses[$m] = ['mes' => $m, 'debito' => 0.0, 'credito' => 0.0, 'recorrente' => 0.0, 'contas' => 0.0, 'renda' => 0.0];
        }

        // À vista por mês
        $s = $conn->prepare("
            SELECT MONTH(data_gasto) AS mes, COALESCE(SUM(valor),0) AS total
            FROM gastos
            WHERE usuario_id = 1 AND metodo_pagamento IN ('Dinheiro','Débito','Pix') AND YEAR(data_gasto) = ?
            GROUP BY MONTH(data_gasto)
        ");
        $s->execute([$ano]);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) { $meses[(int)$r['mes']]['debito'] += (float)$r['total']; }

        // Crédito não parcelado por mês
        $s = $conn->prepare("
            SELECT MONTH(dataVencimento) AS mes, COALESCE(SUM(valor),0) AS total
            FROM gastos
            WHERE usuario_id = 1 AND metodo_pagamento = 'Crédito' AND parcelado = 'N' AND YEAR(dataVencimento) = ?
            GROUP BY MONTH(dataVencimento)
        ");
        $s->execute([$ano]);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) { $meses[(int)$r['mes']]['credito'] += (float)$r['total']; }

        // Parcelas por mês
        $s = $conn->prepare("
            SELECT MONTH(p.data_vencimento) AS mes, COALESCE(SUM(p.valor_parcela),0) AS total
            FROM parcelas p JOIN gastos g ON g.id = p.gasto_id
            WHERE g.usuario_id = 1 AND p.ativo = 'S' AND YEAR(p.data_vencimento) = ?
            GROUP BY MONTH(p.data_vencimento)
        ");
        $s->execute([$ano]);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) { $meses[(int)$r['mes']]['credito'] += (float)$r['total']; }

        // Recorrentes por mês
        $s = $conn->prepare("
            SELECT MONTH(grl.mes_referencia) AS mes, COALESCE(SUM(grl.valor),0) AS total
            FROM gastos_recorrentes_lancamentos grl
            INNER JOIN gastos_recorrentes gr ON gr.id = grl.gasto_recorrente_id
            WHERE gr.ativo = 'S'
              AND (gr.mes_inicio IS NULL OR gr.mes_inicio <= grl.mes_referencia)
              AND YEAR(grl.mes_referencia) = ?
            GROUP BY MONTH(grl.mes_referencia)
        ");
        $s->execute([$ano]);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) { $meses[(int)$r['mes']]['recorrente'] += (float)$r['total']; }

        // Contas a pagar (contas_pessoa) por mês
        try {
            $s = $conn->prepare("
                SELECT MONTH(data) AS mes, COALESCE(SUM(valor),0) AS total
                FROM contas_pessoa
                WHERE usuario_id = 1 AND YEAR(data) = ?
                GROUP BY MONTH(data)
            ");
            $s->execute([$ano]);
            foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) { $meses[(int)$r['mes']]['contas'] += (float)$r['total']; }
        } catch (Exception $e) {}

        // Renda por mês
        try {
            $mult = ['Mensal' => 1, 'Quinzenal' => 2, 'Semanal' => 4.33, 'Anual' => 1/12, 'Único' => 0];
            $s = $conn->prepare("
                SELECT valor, recorrencia, MONTH(data_registro) AS mes_reg
                FROM renda_mensal
                WHERE usuario_id = 1 AND ativo = 'S'
                  AND (recorrencia != 'Único' OR YEAR(data_registro) = ?)
            ");
            $s->execute([$ano]);
            foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $val = (float) $r['valor'];
                if ($r['recorrencia'] === 'Único') {
                    $meses[(int)$r['mes_reg']]['renda'] += $val;
                } else {
                    $mensal = $val * ($mult[$r['recorrencia']] ?? 1);
                    for ($m = 1; $m <= 12; $m++) { $meses[$m]['renda'] += $mensal; }
                }
            }
        } catch (Exception $e) {}

        // Gasto e saldo por mês
        foreach ($meses as &$m) {
            $m['gasto'] = $m['debito'] + $m['credito'] + $m['recorrente'] + $m['contas'];
            $m['saldo'] = $m['renda'] - $m['gasto'];
        }
        unset($m);

        // Por categoria (ano inteiro)
        $s = $conn->prepare("
            SELECT cat.nome, cat.cor, cat.icone, SUM(sub.val) AS total
            FROM (
                SELECT categoria_id, valor AS val FROM gastos
                WHERE usuario_id = 1 AND metodo_pagamento IN ('Dinheiro','Débito','Pix') AND YEAR(data_gasto) = ?
                UNION ALL
                SELECT categoria_id, valor AS val FROM gastos
                WHERE usuario_id = 1 AND metodo_pagamento = 'Crédito' AND parcelado = 'N' AND YEAR(dataVencimento) = ?
                UNION ALL
                SELECT g.categoria_id, p.valor_parcela AS val
                FROM parcelas p JOIN gastos g ON g.id = p.gasto_id
                WHERE g.usuario_id = 1 AND p.ativo = 'S' AND YEAR(p.data_vencimento) = ?
                UNION ALL
                SELECT gr.categoria_id, grl.valor AS val
                FROM gastos_recorrentes_lancamentos grl
                JOIN gastos_recorrentes gr ON gr.id = grl.gasto_recorrente_id
                WHERE gr.ativo = 'S' AND YEAR(grl.mes_referencia) = ?
                UNION ALL
                SELECT cp.categoria_id, cp.valor AS val
                FROM contas_pessoa cp
                WHERE cp.usuario_id = 1 AND cp.categoria_id IS NOT NULL AND YEAR(cp.data) = ?
            ) sub
            JOIN categorias cat ON cat.id = sub.categoria_id
            GROUP BY sub.categoria_id, cat.nome, cat.cor, cat.icone
            ORDER BY total DESC
        ");
        $s->execute([$ano, $ano, $ano, $ano, $ano]);
        $porCategoria = $s->fetchAll(PDO::FETCH_ASSOC);

        $totais = [
            'debito'     => array_sum(array_column($meses, 'debito')),
            'credito'    => array_sum(array_column($meses, 'credito')),
            'recorrente' => array_sum(array_column($meses, 'recorrente')),
            'contas'     => array_sum(array_column($meses, 'contas')),
            'renda'      => array_sum(array_column($meses, 'renda')),
            'gasto'      => array_sum(array_column($meses, 'gasto')),
            'saldo'      => array_sum(array_column($meses, 'saldo')),
        ];

        $mesesArr = array_values($meses);
        $melhor = array_reduce($mesesArr, fn($c, $m) => (!$c || $m['saldo'] > $c['saldo']) ? $m : $c);
        $pior   = array_reduce($mesesArr, fn($c, $m) => (!$c || $m['saldo'] < $c['saldo']) ? $m : $c);

        return [
            'meses'        => $mesesArr,
            'porCategoria' => $porCategoria,
            'totais'       => $totais,
            'melhorMes'    => $melhor,
            'piorMes'      => $pior,
        ];
    }

    public static function editaRecorrentes($id, $nome, $valor, $categoria, $cartao) {
        $conn = Database::getConnection();

        $stmt = $conn->prepare("
            SELECT gr.cartao_id, gr.valor
            FROM gastos_recorrentes gr
            WHERE gr.id = ?
        ");
        $stmt->execute([(int) $id]);
        $atual = $stmt->fetch();

        if (!$atual) {
            return false;
        }

        if ((int) $atual['cartao_id'] !== (int) $cartao || (float) $atual['valor'] !== (float) $valor) {
            $sql = $conn->prepare("UPDATE gastos_recorrentes SET ativo = 'N' WHERE id = ?");
            $sucessoInativar = $sql->execute([(int) $id]);

            if (!$sucessoInativar) {
                return false;
            }

            $adicionar = $conn->prepare("
                INSERT INTO gastos_recorrentes (nome, categoria_id, cartao_id, usuario_id, valor, ativo, mes_inicio)
                VALUES (:desc, :categoria, :cartao, 1, :valor, 'S', :mes_inicio)
            ");

            $queryAdicionar = $adicionar->execute([
                ':categoria'  => (int) $categoria,
                ':desc'       => $nome,
                ':valor'      => $valor,
                ':cartao'     => $cartao ? (int) $cartao : null,
                ':mes_inicio' => date('Y-m-01'),
            ]);

            if ($queryAdicionar) {
                $novoId = $conn->lastInsertId();
                $mesReferencia = date('Y-m-01');

                $lancamento = $conn->prepare("
                    INSERT INTO gastos_recorrentes_lancamentos
                    (gasto_recorrente_id, mes_referencia, valor, nome, categoria_id, cartao_id, usuario_id)
                    VALUES (:gasto_id, :mes, :valor, :nome, :categoria, :cartao, 1)
                ");

                $lancamento->execute([
                    ':gasto_id'  => $novoId,
                    ':mes'       => $mesReferencia,
                    ':valor'     => $valor,
                    ':nome'      => $nome,
                    ':categoria' => (int) $categoria,
                    ':cartao'    => $cartao ? (int) $cartao : null,
                ]);

                return $lancamento;
            }

            return false;
        }

        $sql = $conn->prepare("UPDATE gastos_recorrentes SET categoria_id = ?, nome = ?, ativo = 'S' WHERE id = ?");
        return $sql->execute([(int) $categoria, $nome, (int) $id]);
    }
}
