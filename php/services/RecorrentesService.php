<?php
include_once __DIR__ . '/../../conn/conn.php';

class RecorrentesService {

    public static function lancarRecorrentesDoMes(): void {
        $conn = Database::getConnection();

        $hoje          = date('Y-m-d');
        $mesReferencia = date('Y-m-01');

        $sql = $conn->prepare("
            SELECT gr.*, cc.fechamento_dia
            FROM gastos_recorrentes gr
            LEFT JOIN cartoes_credito cc ON cc.id = gr.cartao_id
            WHERE gr.ativo = 'S'
              AND (gr.mes_inicio IS NULL OR gr.mes_inicio <= ?)
        ");
        $sql->execute([$mesReferencia]);
        $gastos = $sql->fetchAll();

        if (!$gastos) return;

        $verificar = $conn->prepare("
            SELECT COUNT(*) FROM gastos_recorrentes_lancamentos
            WHERE gasto_recorrente_id = ? AND mes_referencia = ?
        ");

        $stmt = $conn->prepare("
            INSERT INTO gastos_recorrentes_lancamentos
            (gasto_recorrente_id, mes_referencia, valor, nome, categoria_id, cartao_id, usuario_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($gastos as $gasto) {
            $fechamentoDia = (int) ($gasto['fechamento_dia'] ?? 0);

            if ($fechamentoDia > 0) {
                $dataFechamento = date('Y-m-') . str_pad($fechamentoDia, 2, '0', STR_PAD_LEFT);
                $mesLancamento  = ($hoje >= $dataFechamento)
                    ? date('Y-m-01', strtotime('+1 month'))
                    : $mesReferencia;
            } else {
                $mesLancamento = $mesReferencia;
            }

            // Só insere se ainda não existe para este recorrente+mês
            $verificar->execute([$gasto['id'], $mesLancamento]);
            if ((int) $verificar->fetchColumn() > 0) continue;

            $stmt->execute([
                $gasto['id'],
                $mesLancamento,
                $gasto['valor'],
                $gasto['nome'],
                $gasto['categoria_id'],
                $gasto['cartao_id'],
                $gasto['usuario_id'],
            ]);
        }
    }
}
