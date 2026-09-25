<?php

include_once __DIR__ . '/../../conn/conn.php';
include_once __DIR__ . '/ContasFixasModel.php';
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
                r.arquivado_em,
                COALESCE(SUM(CASE WHEN cp.pago = 'N' THEN cp.valor ELSE 0 END), 0) AS eu_devo,
                COALESCE(SUM(CASE WHEN cp.pago = 'S' THEN cp.valor ELSE 0 END), 0) AS eu_paguei,
                COUNT(CASE WHEN cp.pago = 'N' THEN 1 END)                           AS qtd_aberto
            FROM responsaveis r
            LEFT JOIN contas_pessoa cp
                ON cp.responsavel_id = r.id AND cp.usuario_id = @uid
                AND MONTH(cp.data) = :mes AND YEAR(cp.data) = :ano
            WHERE r.usuario_id = @uid
            GROUP BY r.id, r.nome, r.cor, r.arquivado_em
            ORDER BY eu_devo DESC, r.nome
        ");
        $stmt->execute([':mes' => $mes, ':ano' => $ano]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // "Ela me deve": mesma lista da aba (despesasMeDeve), para o total e o
        // "em aberto" do cabeçalho baterem sempre com os itens exibidos.
        foreach ($rows as &$r) {
            $itens = self::despesasMeDeve((int) $r['id'], $mes, $ano);
            $total = 0.0; $aberto = 0.0;
            foreach ($itens as $it) {
                $total += $it['valor'];
                if (!$it['recebido']) $aberto += $it['valor'];
            }

            $r['eu_devo']         = (float) $r['eu_devo'];
            $r['eu_paguei']       = (float) $r['eu_paguei'];
            $r['qtd_aberto']      = (int)   $r['qtd_aberto'];

            // Contas fixas que eu pago por meio dela (dinheiro vai para ela)
            foreach (ContasFixasModel::daPessoa((int) $r['id'], $mes, $ano) as $cf) {
                if ($cf['pago']) {
                    $r['eu_paguei'] += $cf['valor'];
                } else {
                    $r['eu_devo']   += $cf['valor'];
                    $r['qtd_aberto']++;
                }
            }
            $r['me_deve']         = $total;
            $r['me_deve_aberto']  = $aberto;
        }
        unset($r);

        // Pessoa arquivada só aparece nos meses em que tem movimento (histórico).
        return array_values(array_filter($rows, function ($r) {
            return !$r['arquivado_em'] || ($r['eu_devo'] + $r['eu_paguei'] + $r['me_deve']) > 0;
        }));
    }

    /**
     * Despesas no nome da pessoa ("ela me deve") com o status de recebimento:
     *  - Crédito (à vista, parcela ou recorrente com cartão): quitado quando a
     *    fatura daquele cartão/mês está marcada como paga (faturas_pagas).
     *  - Pix/débito/dinheiro e recorrente sem cartão: marcado à mão (recebido_em).
     * Cada item traz `forma` ('fatura' | 'manual') e, se manual, `alvo` + `id`
     * para o botão de recebido.
     */
    public static function despesasMeDeve(int $responsavelId, int $mes, int $ano): array {
        if (ConfigModel::antesDoMarco($mes, $ano)) return [];
        $conn = Database::getConnection();

        // Pagamento da fatura do cartão no mês de vencimento informado
        $faturaPaga = function (string $cartao, string $dataVenc): string {
            return "(SELECT fp.data_pagamento FROM faturas_pagas fp
                     WHERE fp.cartao_id = {$cartao} AND fp.usuario_id = @uid
                       AND fp.mes = MONTH({$dataVenc}) AND fp.ano = YEAR({$dataVenc}))";
        };

        // Não-crédito: filtra por data_gasto; recebimento manual
        $stmt = $conn->prepare("
            SELECT g.id, g.descricao AS nome, g.valor, g.data_gasto AS data,
                   g.metodo_pagamento AS metodo, cc.nome_cartao, g.cartao_id,
                   cat.nome AS categoria, cat.cor AS cat_cor, 'avulso' AS origem,
                   'manual' AS forma, 'gasto' AS alvo, g.recebido_em
            FROM gastos g
            LEFT JOIN cartoes_credito cc  ON cc.id  = g.cartao_id
            LEFT JOIN categorias cat ON cat.id = g.categoria_id
            WHERE g.responsavel_id = :rid AND g.usuario_id = @uid
              AND g.metodo_pagamento != 'Crédito'
              AND MONTH(g.data_gasto) = :mes AND YEAR(g.data_gasto) = :ano
        ");
        $stmt->execute([':rid' => $responsavelId, ':mes' => $mes, ':ano' => $ano]);
        $avulsas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Crédito não parcelado: filtra por dataVencimento; quitado pela fatura
        $stmtCred = $conn->prepare("
            SELECT g.id, g.descricao AS nome, g.valor, g.dataVencimento AS data,
                   g.metodo_pagamento AS metodo, cc.nome_cartao, g.cartao_id,
                   cat.nome AS categoria, cat.cor AS cat_cor, 'avulso' AS origem,
                   'fatura' AS forma, NULL AS alvo, " . $faturaPaga('g.cartao_id', 'g.dataVencimento') . " AS recebido_em
            FROM gastos g
            LEFT JOIN cartoes_credito cc  ON cc.id  = g.cartao_id
            LEFT JOIN categorias cat ON cat.id = g.categoria_id
            WHERE g.responsavel_id = :rid AND g.usuario_id = @uid
              AND g.metodo_pagamento = 'Crédito' AND g.parcelado = 'N'
              AND MONTH(g.dataVencimento) = :mes AND YEAR(g.dataVencimento) = :ano
        ");
        $stmtCred->execute([':rid' => $responsavelId, ':mes' => $mes, ':ano' => $ano]);
        $avulsas = array_merge($avulsas, $stmtCred->fetchAll(PDO::FETCH_ASSOC));

        // Crédito parcelado: filtra por data_vencimento da parcela; quitado pela fatura
        $stmtParc = $conn->prepare("
            SELECT g.id, g.descricao AS nome, p.valor_parcela AS valor, p.data_vencimento AS data,
                   g.metodo_pagamento AS metodo, cc.nome_cartao, g.cartao_id,
                   cat.nome AS categoria, cat.cor AS cat_cor, 'avulso' AS origem,
                   'fatura' AS forma, NULL AS alvo, " . $faturaPaga('g.cartao_id', 'p.data_vencimento') . " AS recebido_em
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

        // Recorrentes: com cartão → fatura; sem cartão → manual (no lançamento do mês)
        $stmt2 = $conn->prepare("
            SELECT grl.id, gr.id AS recorrente_id, gr.nome, grl.valor, grl.mes_referencia AS data,
                   'Recorrente' AS metodo, cc.nome_cartao, gr.cartao_id,
                   cat.nome AS categoria, cat.cor AS cat_cor, 'recorrente' AS origem,
                   IF(gr.cartao_id IS NULL, 'manual', 'fatura') AS forma,
                   IF(gr.cartao_id IS NULL, 'lancamento', NULL) AS alvo,
                   IF(gr.cartao_id IS NULL, grl.recebido_em, " . $faturaPaga('gr.cartao_id', 'grl.mes_referencia') . ") AS recebido_em
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
        foreach ($todos as &$d) {
            $d['valor']    = (float) $d['valor'];
            $d['recebido'] = !empty($d['recebido_em']);
        }
        return $todos;
    }

    /**
     * Marca/desmarca como recebido um item "ela me deve" de recebimento manual.
     * $alvo: 'gasto' (Pix/débito/dinheiro) ou 'lancamento' (recorrente sem cartão).
     * Crédito é recusado de propósito: o status dele vem da fatura paga.
     */
    /**
     * Tira a despesa da pessoa ("não é mais dela"): a despesa continua sendo minha,
     * só some do "ela me deve". Parcelado sai inteiro (todas as parcelas); recorrente
     * sai em todos os meses.
     */
    public static function desvincular(string $tipo, int $id): bool {
        $conn = Database::getConnection();
        if ($tipo === 'gasto') {
            $stmt = $conn->prepare("UPDATE gastos SET responsavel_id = NULL, recebido_em = NULL WHERE id = :id AND usuario_id = @uid");
        } elseif ($tipo === 'recorrente') {
            $stmt = $conn->prepare("UPDATE gastos_recorrentes SET responsavel_id = NULL WHERE id = :id AND usuario_id = @uid");
        } else {
            return false;
        }
        return $stmt->execute([':id' => $id]);
    }

    public static function marcarRecebido(string $alvo, int $id, bool $recebido): bool {
        $conn = Database::getConnection();
        $data = $recebido ? date('Y-m-d') : null;

        if ($alvo === 'gasto') {
            $stmt = $conn->prepare("
                UPDATE gastos SET recebido_em = :data
                WHERE id = :id AND usuario_id = @uid
                  AND responsavel_id IS NOT NULL AND metodo_pagamento != 'Crédito'
            ");
        } elseif ($alvo === 'lancamento') {
            $stmt = $conn->prepare("
                UPDATE gastos_recorrentes_lancamentos grl
                INNER JOIN gastos_recorrentes gr ON gr.id = grl.gasto_recorrente_id
                SET grl.recebido_em = :data
                WHERE grl.id = :id AND gr.usuario_id = @uid
                  AND gr.responsavel_id IS NOT NULL AND gr.cartao_id IS NULL
            ");
        } else {
            return false;
        }
        $stmt->execute([':data' => $data, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
