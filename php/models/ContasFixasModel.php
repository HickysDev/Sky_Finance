<?php

include_once __DIR__ . '/../../conn/conn.php';
include_once __DIR__ . '/ConfigModel.php';

class ContasFixasModel {

    /**
     * Condição SQL "a conta fixa vale no mês": criada até o mês e ativa nele
     * (ativa hoje, ou inativada depois do mês), OU com pagamento registrado no
     * mês. Única regra usada por dashboard, finanças, resumo anual e a tela
     * mensal — antes cada conta ativa valia em todos os meses e sumia de todo
     * o histórico ao ser inativada.
     * $alias é o alias de contas_fixas na query; mês/ano viram literais seguros.
     */
    public static function sqlValeNoMes(string $alias, int $mes, int $ano): string {
        $alvo = sprintf('%04d-%02d-01', $ano, $mes);
        $mes  = (int) $mes;
        $ano  = (int) $ano;
        return "(
            (DATE_FORMAT({$alias}.created_at, '%Y-%m-01') <= '{$alvo}'
             AND ({$alias}.ativo = 'S' OR {$alias}.inativado_em > '{$alvo}'))
            OR EXISTS (SELECT 1 FROM contas_fixas_pagamentos cfp_v
                       WHERE cfp_v.conta_fixa_id = {$alias}.id AND cfp_v.mes = {$mes} AND cfp_v.ano = {$ano})
        )";
    }

    /**
     * Valor da conta no mês: o valor pago, se houver pagamento registrado; senão o
     * valor cadastrado. Assim, mudar o valor da conta não reescreve meses já pagos.
     */
    public static function sqlValorNoMes(string $alias, int $mes, int $ano): string {
        $mes = (int) $mes;
        $ano = (int) $ano;
        return "COALESCE((SELECT cfp_x.valor_pago FROM contas_fixas_pagamentos cfp_x
                          WHERE cfp_x.conta_fixa_id = {$alias}.id AND cfp_x.mes = {$mes} AND cfp_x.ano = {$ano}),
                         {$alias}.valor)";
    }

    /** Soma das contas fixas que valem no mês (para os totais de gasto). */
    public static function totalMes(int $mes, int $ano): float {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(" . self::sqlValorNoMes('cf', $mes, $ano) . "), 0) FROM contas_fixas cf
            WHERE cf.usuario_id = @uid AND " . self::sqlValeNoMes('cf', $mes, $ano)
        );
        $stmt->execute();
        return (float) $stmt->fetchColumn();
    }

    public static function listar(): array {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT cf.*, r.nome AS responsavel_nome
            FROM contas_fixas cf
            LEFT JOIN responsaveis r ON r.id = cf.responsavel_id
            WHERE cf.usuario_id = @uid AND cf.arquivado_em IS NULL ORDER BY cf.nome ASC
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['valor'] = (float) $r['valor'];
        }
        return $rows;
    }

    // $resp = quem paga por padrão (NULL = eu). Só vale para meses ainda não pagos:
    // cada pagamento grava o responsável daquele mês.
    public static function adicionar(string $nome, float $valor, int $dia, string $cor, ?int $resp = null): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            INSERT INTO contas_fixas (usuario_id, nome, valor, dia_vencimento, cor, responsavel_id)
            VALUES (@uid, :nome, :valor, :dia, :cor, :resp)
        ");
        return $stmt->execute([':nome' => trim($nome), ':valor' => $valor, ':dia' => $dia, ':cor' => $cor, ':resp' => $resp]);
    }

    public static function editar(int $id, string $nome, float $valor, int $dia, string $cor, ?int $resp = null): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            UPDATE contas_fixas SET nome = :nome, valor = :valor, dia_vencimento = :dia, cor = :cor, responsavel_id = :resp
            WHERE id = :id AND usuario_id = @uid
        ");
        return $stmt->execute([':nome' => trim($nome), ':valor' => $valor, ':dia' => $dia, ':cor' => $cor, ':resp' => $resp, ':id' => $id]);
    }

    /** Quem paga por padrão (NULL = eu). */
    public static function responsavelPadrao(int $id): ?int {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT responsavel_id FROM contas_fixas WHERE id = :id AND usuario_id = @uid");
        $stmt->execute([':id' => $id]);
        $v = $stmt->fetchColumn();
        return $v ? (int) $v : null;
    }

    /**
     * Contas fixas que passam por uma pessoa no mês ("devo a ela"): paga no mês com
     * o dinheiro indo para ela, ou ainda não paga e ela é a responsável padrão.
     */
    public static function daPessoa(int $responsavelId, int $mes, int $ano): array {
        if (ConfigModel::antesDoMarco($mes, $ano)) return [];
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT cf.id, cf.nome, cf.dia_vencimento, " . self::sqlValorNoMes('cf', $mes, $ano) . " AS valor,
                   cfp.id AS pagamento_id, cfp.data_pagamento
            FROM contas_fixas cf
            LEFT JOIN contas_fixas_pagamentos cfp
                ON cfp.conta_fixa_id = cf.id AND cfp.mes = :mes AND cfp.ano = :ano
            WHERE cf.usuario_id = @uid AND " . self::sqlValeNoMes('cf', $mes, $ano) . "
              AND ( (cfp.id IS NOT NULL AND cfp.pulado = 'N' AND cfp.responsavel_id = :rid1)
                 OR (cfp.id IS NULL AND cf.responsavel_id = :rid2) )
            ORDER BY cf.dia_vencimento, cf.nome
        ");
        $stmt->execute([':mes' => $mes, ':ano' => $ano, ':rid1' => $responsavelId, ':rid2' => $responsavelId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $dia = min((int) $r['dia_vencimento'], (int) date('t', mktime(0, 0, 0, $mes, 1, $ano)));
            $r['valor'] = (float) $r['valor'];
            $r['pago']  = $r['pagamento_id'] !== null;
            $r['data']  = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
        }
        return $rows;
    }

    public static function toggleAtivo(int $id): bool {
        $conn = Database::getConnection();
        // Inativar vale a partir do mês atual (meses anteriores continuam contando);
        // reativar limpa a data.
        $stmt = $conn->prepare("
            UPDATE contas_fixas
            SET inativado_em = IF(ativo = 'S', DATE_FORMAT(CURDATE(), '%Y-%m-01'), NULL),
                ativo        = IF(ativo = 'S', 'N', 'S')
            WHERE id = :id AND usuario_id = @uid
        ");
        return $stmt->execute([':id' => $id]);
    }

    public static function excluir(int $id): bool {
        $conn = Database::getConnection();
        // Arquiva em vez de apagar: o DELETE levava os pagamentos junto (CASCADE) e a
        // conta sumia de todos os meses. Arquivar = inativar a partir deste mês
        // (mantendo uma inativação anterior) + esconder da lista de cadastro.
        $stmt = $conn->prepare("
            UPDATE contas_fixas
            SET arquivado_em = CURDATE(),
                inativado_em = IF(ativo = 'S', DATE_FORMAT(CURDATE(), '%Y-%m-01'), inativado_em),
                ativo        = 'N'
            WHERE id = :id AND usuario_id = @uid
        ");
        return $stmt->execute([':id' => $id]);
    }

    public static function resumoMes(int $mes, int $ano): array {
        if (ConfigModel::antesDoMarco($mes, $ano)) return [];
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT cf.id, cf.nome, cf.valor, cf.dia_vencimento, cf.cor, cf.ativo,
                   cfp.id AS pagamento_id, cfp.data_pagamento, cfp.valor_pago, cfp.pulado,
                   cf.responsavel_id AS responsavel_padrao,
                   -- pago: para quem foi o dinheiro naquele mês; em aberto: o padrão
                   IF(cfp.id IS NULL, cf.responsavel_id, cfp.responsavel_id) AS responsavel_id,
                   r.nome AS responsavel_nome
            FROM contas_fixas cf
            LEFT JOIN contas_fixas_pagamentos cfp
                ON cfp.conta_fixa_id = cf.id AND cfp.mes = :mes AND cfp.ano = :ano AND cfp.usuario_id = @uid
            LEFT JOIN responsaveis r ON r.id = IF(cfp.id IS NULL, cf.responsavel_id, cfp.responsavel_id)
            WHERE cf.usuario_id = @uid AND " . self::sqlValeNoMes('cf', $mes, $ano) . "
            ORDER BY cf.nome ASC
        ");
        $stmt->execute([':mes' => $mes, ':ano' => $ano]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['valor']      = (float) $r['valor'];
            $r['valor_pago'] = $r['valor_pago'] !== null ? (float) $r['valor_pago'] : null;
            $r['pulado']     = $r['pulado'] === 'S';
            $r['pago']       = $r['pagamento_id'] !== null && !$r['pulado'];
        }
        return $rows;
    }

    // $resp = para quem foi o dinheiro neste mês (NULL = paguei eu mesmo).
    public static function marcarPago(int $contaFixaId, int $mes, int $ano, string $data, float $valorPago, ?int $resp = null): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            INSERT INTO contas_fixas_pagamentos (conta_fixa_id, usuario_id, mes, ano, data_pagamento, valor_pago, responsavel_id)
            VALUES (:cid, @uid, :mes, :ano, :data, :valor, :resp)
            ON DUPLICATE KEY UPDATE data_pagamento = :data2, valor_pago = :valor2, pulado = 'N', responsavel_id = :resp2
        ");
        return $stmt->execute([
            ':cid'   => $contaFixaId, ':mes' => $mes, ':ano' => $ano,
            ':data'  => $data,        ':valor' => $valorPago,  ':resp'  => $resp,
            ':data2' => $data,        ':valor2' => $valorPago, ':resp2' => $resp,
        ]);
    }

    /** "Não vou pagar este mês": registra o mês com valor 0, fora dos totais e dos alertas. */
    public static function pularMes(int $contaFixaId, int $mes, int $ano): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            INSERT INTO contas_fixas_pagamentos (conta_fixa_id, usuario_id, mes, ano, data_pagamento, valor_pago, pulado)
            SELECT cf.id, @uid, :mes, :ano, CURDATE(), 0, 'S'
            FROM contas_fixas cf WHERE cf.id = :cid AND cf.usuario_id = @uid
            ON DUPLICATE KEY UPDATE data_pagamento = CURDATE(), valor_pago = 0, pulado = 'S', responsavel_id = NULL
        ");
        return $stmt->execute([':cid' => $contaFixaId, ':mes' => $mes, ':ano' => $ano]);
    }

    public static function desmarcarPago(int $contaFixaId, int $mes, int $ano): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            DELETE FROM contas_fixas_pagamentos
            WHERE conta_fixa_id = :cid AND mes = :mes AND ano = :ano AND usuario_id = @uid
        ");
        return $stmt->execute([':cid' => $contaFixaId, ':mes' => $mes, ':ano' => $ano]);
    }

    public static function proximosVencimentos(int $dias = 7): array {
        $conn = Database::getConnection();
        $mes  = (int) date('n');
        $ano  = (int) date('Y');
        $hoje = (int) date('j');

        $stmt = $conn->prepare("
            SELECT cf.id, cf.nome, cf.valor, cf.dia_vencimento, cf.cor
            FROM contas_fixas cf
            LEFT JOIN contas_fixas_pagamentos cfp
                ON cfp.conta_fixa_id = cf.id AND cfp.mes = :mes AND cfp.ano = :ano AND cfp.usuario_id = @uid
            WHERE cf.usuario_id = @uid AND cf.ativo = 'S' AND cfp.id IS NULL
            ORDER BY cf.dia_vencimento ASC
        ");
        $stmt->execute([':mes' => $mes, ':ano' => $ano]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $r) {
            $diff = (int) $r['dia_vencimento'] - $hoje;
            if ($diff > $dias) continue;
            $r['dias_restantes'] = $diff;
            $r['valor']          = (float) $r['valor'];
            $result[] = $r;
        }
        return $result;
    }
}
