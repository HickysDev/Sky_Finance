<?php
include_once __DIR__ . '/../../conn/conn.php';
include_once __DIR__ . '/../models/GastosModel.php';

class RecorrentesService {

    /**
     * Lança os recorrentes do mês corrente para o usuário logado.
     * Chamado pelo header em toda página.
     *
     * Delega para GastosModel::gerarLancamentosParaMes() de propósito: antes
     * havia aqui uma segunda implementação que (a) varria gastos_recorrentes de
     * TODOS os usuários, sem filtrar por @uid, e (b) usava o dia de fechamento
     * do cartão para empurrar o lançamento para o mês seguinte — o que fazia o
     * mesmo recorrente aparecer em dois meses, já que o model gera pelo mês
     * visualizado. Uma regra só, no lugar de duas divergentes.
     */
    public static function lancarRecorrentesDoMes(): void {
        GastosModel::gerarLancamentosParaMes((int) date('n'), (int) date('Y'));
    }
}
