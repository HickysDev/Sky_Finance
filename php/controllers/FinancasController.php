<?php
include_once __DIR__ . '/../models/FinancasModel.php';

header('Content-Type: application/json; charset=utf-8');

$acao    = $_POST['acao'] ?? $_GET['acao'] ?? '';
$Financas = new FinancasModel();
$retorno  = null;

switch ($acao) {
    case 'buscarRendas':
        $mes     = (int) ($_POST['mes'] ?? date('n'));
        $ano     = (int) ($_POST['ano'] ?? date('Y'));
        $retorno = $Financas->buscarRendas($mes, $ano);
        break;

    case 'adicionarRenda':
        $retorno = $Financas->adicionarRenda($_POST);
        break;

    case 'editarRenda':
        $retorno = $Financas->editarRenda($_POST);
        break;

    case 'toggleAtivo':
        $retorno = $Financas->toggleAtivo((int) ($_POST['id'] ?? 0));
        break;

    case 'removerRenda':
        $retorno = $Financas->removerRenda((int) ($_POST['id'] ?? 0));
        break;

    case 'totalGastosMes':
        $mes     = (int) ($_POST['mes'] ?? date('n'));
        $ano     = (int) ($_POST['ano'] ?? date('Y'));
        $retorno = $Financas->totalGastosMes($mes, $ano);
        break;

    default:
        http_response_code(400);
        $retorno = ['erro' => 'Ação inválida'];
        break;
}

echo json_encode($retorno);
