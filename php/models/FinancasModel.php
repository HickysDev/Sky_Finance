<?php

include_once __DIR__ . '/../../conn/conn.php';

class FinancasModel {

    private function parseValor(string $raw): float {
        $clean = str_replace(['R$', ' ', '.'], '', $raw);
        return (float) str_replace(',', '.', $clean);
    }

    public function buscarRendas(int $mes, int $ano): array {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT *, (mes IS NULL) AS recorrente
            FROM renda_mensal
            WHERE usuario_id = 1
              AND (mes IS NULL OR (mes = :mes AND ano = :ano))
            ORDER BY (mes IS NULL) DESC, ativo DESC, id DESC
        ");
        $stmt->execute([':mes' => $mes, ':ano' => $ano]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function adicionarRenda(array $data): bool {
        $conn = Database::getConnection();
        $mes  = isset($data['mes']) && $data['mes'] !== '' ? (int) $data['mes'] : null;
        $ano  = isset($data['ano']) && $data['ano'] !== '' ? (int) $data['ano'] : null;
        $stmt = $conn->prepare("
            INSERT INTO renda_mensal (usuario_id, descricao, tipo, recorrencia, valor, mes, ano, data_registro)
            VALUES (1, :descricao, :tipo, :recorrencia, :valor, :mes, :ano, CURDATE())
        ");
        return $stmt->execute([
            ':descricao'   => trim($data['descricao']),
            ':tipo'        => $data['tipo'],
            ':recorrencia' => $data['recorrencia'] ?? 'Mensal',
            ':valor'       => $this->parseValor($data['valor'] ?? '0'),
            ':mes'         => $mes,
            ':ano'         => $ano,
        ]);
    }

    public function editarRenda(array $data): bool {
        $conn = Database::getConnection();
        $mes  = isset($data['mes']) && $data['mes'] !== '' ? (int) $data['mes'] : null;
        $ano  = isset($data['ano']) && $data['ano'] !== '' ? (int) $data['ano'] : null;
        $stmt = $conn->prepare("
            UPDATE renda_mensal
            SET descricao   = :descricao,
                tipo        = :tipo,
                recorrencia = :recorrencia,
                valor       = :valor,
                mes         = :mes,
                ano         = :ano
            WHERE id = :id AND usuario_id = 1
        ");
        return $stmt->execute([
            ':descricao'   => trim($data['descricao']),
            ':tipo'        => $data['tipo'],
            ':recorrencia' => $data['recorrencia'] ?? 'Mensal',
            ':valor'       => $this->parseValor($data['valor'] ?? '0'),
            ':mes'         => $mes,
            ':ano'         => $ano,
            ':id'          => (int) $data['id'],
        ]);
    }

    public function toggleAtivo(int $id): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            UPDATE renda_mensal
            SET ativo = IF(ativo = 'S', 'N', 'S')
            WHERE id = :id AND usuario_id = 1
        ");
        return $stmt->execute([':id' => $id]);
    }

    public function removerRenda(int $id): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("DELETE FROM renda_mensal WHERE id = :id AND usuario_id = 1");
        return $stmt->execute([':id' => $id]);
    }

    public function totalGastosMes(int $mes, int $ano): float {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(v), 0) FROM (
                SELECT valor AS v
                FROM gastos
                WHERE usuario_id = 1
                  AND metodo_pagamento IN ('Dinheiro','Débito','Pix')
                  AND MONTH(data_gasto) = :m1 AND YEAR(data_gasto) = :a1

                UNION ALL

                SELECT valor AS v
                FROM gastos
                WHERE usuario_id = 1
                  AND metodo_pagamento = 'Crédito' AND parcelado = 'N'
                  AND MONTH(dataVencimento) = :m2 AND YEAR(dataVencimento) = :a2

                UNION ALL

                SELECT p.valor_parcela AS v
                FROM gastos g
                INNER JOIN parcelas p ON p.gasto_id = g.id
                WHERE g.usuario_id = 1
                  AND g.metodo_pagamento = 'Crédito' AND g.parcelado = 'S'
                  AND MONTH(p.data_vencimento) = :m3 AND YEAR(p.data_vencimento) = :a3

                UNION ALL

                SELECT grl.valor AS v
                FROM gastos_recorrentes_lancamentos grl
                INNER JOIN gastos_recorrentes gr ON gr.id = grl.gasto_recorrente_id
                WHERE gr.ativo = 'S'
                  AND MONTH(grl.mes_referencia) = :m4 AND YEAR(grl.mes_referencia) = :a4
            ) t
        ");
        $stmt->execute([
            ':m1' => $mes, ':a1' => $ano,
            ':m2' => $mes, ':a2' => $ano,
            ':m3' => $mes, ':a3' => $ano,
            ':m4' => $mes, ':a4' => $ano,
        ]);
        return (float) $stmt->fetchColumn();
    }
}
