<?php
include_once __DIR__ . '/../../conn/conn.php';

class RecorrentesService {

    public static function lancarRecorrentesDoMes(): void {
        $conn = Database::getConnection();

        $hoje = date('Y-m-d');
        $mesReferencia = date('Y-m-01');

        $verificar = $conn->prepare("
            SELECT COUNT(*) as total
            FROM gastos_recorrentes_lancamentos
            WHERE mes_referencia = ?
        ");
        $verificar->execute([$mesReferencia]);
        $resultado = $verificar->fetch();

        if ($resultado['total'] > 0) {
            return;
        }

        $sql = $conn->query("
            SELECT gr.*, cc.fechamento_dia
            FROM gastos_recorrentes gr
            LEFT JOIN cartoes_credito cc ON cc.id = gr.cartao_id
            WHERE gr.ativo = 'S'
        ");
        $gastos = $sql->fetchAll();

        $stmt = $conn->prepare("
            INSERT INTO gastos_recorrentes_lancamentos
            (gasto_recorrente_id, mes_referencia, valor, nome, categoria_id, cartao_id, usuario_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($gastos as $gasto) {
            $fechamentoDia = (int) $gasto['fechamento_dia'];
            $dataFechamento = date('Y-m-') . str_pad($fechamentoDia, 2, '0', STR_PAD_LEFT);

            $mesLancamento = ($hoje >= $dataFechamento)
                ? date('Y-m-01', strtotime('+1 month'))
                : $mesReferencia;

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
