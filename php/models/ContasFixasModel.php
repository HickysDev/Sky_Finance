<?php

include_once __DIR__ . '/../../conn/conn.php';
include_once __DIR__ . '/ConfigModel.php';

class ContasFixasModel {

    public static function listar(): array {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT * FROM contas_fixas WHERE usuario_id = 1 ORDER BY dia_vencimento ASC, nome ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['valor'] = (float) $r['valor'];
        }
        return $rows;
    }

    public static function adicionar(string $nome, float $valor, int $dia, string $cor): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            INSERT INTO contas_fixas (usuario_id, nome, valor, dia_vencimento, cor)
            VALUES (1, :nome, :valor, :dia, :cor)
        ");
        return $stmt->execute([':nome' => trim($nome), ':valor' => $valor, ':dia' => $dia, ':cor' => $cor]);
    }

    public static function editar(int $id, string $nome, float $valor, int $dia, string $cor): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            UPDATE contas_fixas SET nome = :nome, valor = :valor, dia_vencimento = :dia, cor = :cor
            WHERE id = :id AND usuario_id = 1
        ");
        return $stmt->execute([':nome' => trim($nome), ':valor' => $valor, ':dia' => $dia, ':cor' => $cor, ':id' => $id]);
    }

    public static function toggleAtivo(int $id): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE contas_fixas SET ativo = 1 - ativo WHERE id = :id AND usuario_id = 1");
        return $stmt->execute([':id' => $id]);
    }

    public static function excluir(int $id): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("DELETE FROM contas_fixas WHERE id = :id AND usuario_id = 1");
        return $stmt->execute([':id' => $id]);
    }

    public static function resumoMes(int $mes, int $ano): array {
        if (ConfigModel::antesDoMarco($mes, $ano)) return [];
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT cf.id, cf.nome, cf.valor, cf.dia_vencimento, cf.cor, cf.ativo,
                   cfp.id AS pagamento_id, cfp.data_pagamento, cfp.valor_pago
            FROM contas_fixas cf
            LEFT JOIN contas_fixas_pagamentos cfp
                ON cfp.conta_fixa_id = cf.id AND cfp.mes = :mes AND cfp.ano = :ano AND cfp.usuario_id = 1
            WHERE cf.usuario_id = 1 AND cf.ativo = 1
            ORDER BY cf.dia_vencimento ASC, cf.nome ASC
        ");
        $stmt->execute([':mes' => $mes, ':ano' => $ano]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['valor']      = (float) $r['valor'];
            $r['valor_pago'] = $r['valor_pago'] !== null ? (float) $r['valor_pago'] : null;
            $r['pago']       = $r['pagamento_id'] !== null;
        }
        return $rows;
    }

    public static function marcarPago(int $contaFixaId, int $mes, int $ano, string $data, float $valorPago): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            INSERT INTO contas_fixas_pagamentos (conta_fixa_id, usuario_id, mes, ano, data_pagamento, valor_pago)
            VALUES (:cid, 1, :mes, :ano, :data, :valor)
            ON DUPLICATE KEY UPDATE data_pagamento = :data2, valor_pago = :valor2
        ");
        return $stmt->execute([
            ':cid'   => $contaFixaId, ':mes' => $mes, ':ano' => $ano,
            ':data'  => $data,        ':valor' => $valorPago,
            ':data2' => $data,        ':valor2' => $valorPago,
        ]);
    }

    public static function desmarcarPago(int $contaFixaId, int $mes, int $ano): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            DELETE FROM contas_fixas_pagamentos
            WHERE conta_fixa_id = :cid AND mes = :mes AND ano = :ano AND usuario_id = 1
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
                ON cfp.conta_fixa_id = cf.id AND cfp.mes = :mes AND cfp.ano = :ano AND cfp.usuario_id = 1
            WHERE cf.usuario_id = 1 AND cf.ativo = 1 AND cfp.id IS NULL
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
