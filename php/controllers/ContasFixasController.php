<?php

include_once __DIR__ . '/../models/ContasFixasModel.php';

header('Content-Type: application/json; charset=utf-8');

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';
$id   = (int) ($_POST['id']  ?? 0);
$mes  = (int) ($_POST['mes'] ?? $_GET['mes'] ?? date('n'));
$ano  = (int) ($_POST['ano'] ?? $_GET['ano'] ?? date('Y'));
$nome = trim($_POST['nome'] ?? '');
$dia  = (int) ($_POST['dia_vencimento'] ?? 1);
$cor  = $_POST['cor'] ?? '#3B82F6';
$data = $_POST['data'] ?? date('Y-m-d');

$valor = 0.0;
if (!empty($_POST['valor'])) {
    $v = str_replace(['R$', ' ', '.'], '', $_POST['valor']);
    $valor = (float) str_replace(',', '.', $v);
}

$valorPago = 0.0;
if (!empty($_POST['valor_pago'])) {
    $v = str_replace(['R$', ' ', '.'], '', $_POST['valor_pago']);
    $valorPago = (float) str_replace(',', '.', $v);
}

$retorno = null;

switch ($acao) {

    case 'listar':
        $retorno = ContasFixasModel::listar();
        break;

    case 'adicionar':
        if (!$nome || $valor <= 0 || $dia < 1 || $dia > 31) {
            http_response_code(400); echo json_encode(['erro' => 'Dados inválidos']); exit;
        }
        $retorno = ContasFixasModel::adicionar($nome, $valor, $dia, $cor);
        break;

    case 'editar':
        if (!$id || !$nome) {
            http_response_code(400); echo json_encode(['erro' => 'Dados inválidos']); exit;
        }
        $retorno = ContasFixasModel::editar($id, $nome, $valor, $dia, $cor);
        break;

    case 'toggleAtivo':
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }
        $retorno = ContasFixasModel::toggleAtivo($id);
        break;

    case 'excluir':
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }
        $retorno = ContasFixasModel::excluir($id);
        break;

    case 'resumoMes':
        $retorno = ContasFixasModel::resumoMes($mes, $ano);
        break;

    case 'marcarPago':
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }
        $vp = $valorPago > 0 ? $valorPago : $valor;
        $retorno = ContasFixasModel::marcarPago($id, $mes, $ano, $data, $vp);
        break;

    case 'desmarcarPago':
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }
        $retorno = ContasFixasModel::desmarcarPago($id, $mes, $ano);
        break;

    case 'proximosVencimentos':
        $dias    = (int) ($_POST['dias'] ?? 7);
        $retorno = ContasFixasModel::proximosVencimentos($dias);
        break;

    default:
        http_response_code(400);
        $retorno = ['erro' => 'Ação inválida'];
        break;
}

echo json_encode($retorno);
