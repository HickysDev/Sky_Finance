<?php
require_once __DIR__ . '/../../conn/config.php';
require_once __DIR__ . '/../models/CofrinhoModel.php';

header('Content-Type: application/json; charset=utf-8');

$acao = $_POST['acao'] ?? '';

switch ($acao) {
    case 'listar':
        echo json_encode(CofrinhoModel::listar());
        break;

    case 'salvar':
        $id = (int) ($_POST['id'] ?? 0);
        echo json_encode(CofrinhoModel::salvar($_POST, $id));
        break;

    case 'remover':
        echo json_encode(CofrinhoModel::remover((int) ($_POST['id'] ?? 0)));
        break;

    case 'aporte':
        $cofrinhoId = (int) ($_POST['cofrinho_id'] ?? 0);
        $raw   = str_replace(['R$', ' ', '.'], '', $_POST['valor'] ?? '0');
        $valor = (float) str_replace(',', '.', $raw);
        $data  = $_POST['data_aporte'] ?? date('Y-m-d');
        $obs   = trim($_POST['observacao'] ?? '');
        echo json_encode(CofrinhoModel::aporte($cofrinhoId, $valor, $data, $obs));
        break;

    case 'dashboard':
        $mes = (int) ($_POST['mes'] ?? date('n'));
        $ano = (int) ($_POST['ano'] ?? date('Y'));
        echo json_encode(CofrinhoModel::resumoDashboard($mes, $ano));
        break;

    case 'totalAportesMes':
        $mes = (int) ($_POST['mes'] ?? date('n'));
        $ano = (int) ($_POST['ano'] ?? date('Y'));
        echo json_encode(CofrinhoModel::totalAportesMes($mes, $ano));
        break;

    case 'retirar':
        $cofrinhoId = (int) ($_POST['cofrinho_id'] ?? 0);
        $raw   = str_replace(['R$', ' ', '.'], '', $_POST['valor'] ?? '0');
        $valor = (float) str_replace(',', '.', $raw);
        $data  = $_POST['data_aporte'] ?? date('Y-m-d');
        $obs   = trim($_POST['observacao'] ?? '');
        echo json_encode(CofrinhoModel::retirar($cofrinhoId, $valor, $data, $obs));
        break;

    case 'buscarAportes':
        echo json_encode(CofrinhoModel::buscarAportes((int) ($_POST['id'] ?? 0)));
        break;

    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Ação inválida']);
}
