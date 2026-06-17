<?php
require_once __DIR__ . '/../../conn/config.php';
require_once __DIR__ . '/../models/OrcamentoModel.php';

header('Content-Type: application/json');

$acao = $_POST['acao'] ?? '';

switch ($acao) {

    case 'buscar':
        $mes = (int) ($_POST['mes'] ?? date('n'));
        $ano = (int) ($_POST['ano'] ?? date('Y'));
        echo json_encode(OrcamentoModel::buscarComGasto($mes, $ano));
        break;

    case 'salvar':
        $cat    = (int) ($_POST['categoria_id'] ?? 0);
        $limite = trim($_POST['valor_limite'] ?? '');
        $meses  = trim($_POST['meses'] ?? '');
        $anos   = trim($_POST['anos']  ?? '');
        $id     = (int) ($_POST['id']  ?? 0);
        if (!$cat || $limite === '') { echo json_encode(false); break; }
        echo json_encode(OrcamentoModel::salvar($cat, $limite, $meses, $anos, $id));
        break;

    case 'remover':
        $id = (int) ($_POST['id'] ?? 0);
        echo json_encode(OrcamentoModel::remover($id));
        break;

    default:
        echo json_encode(false);
}
