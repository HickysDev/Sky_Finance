<?php
include_once __DIR__ . '/../models/CartoesModel.php';

header('Content-Type: application/json; charset=utf-8');

$acao     = $_POST['acao'] ?? $_GET['acao'] ?? '';
$id       = $_POST['id']   ?? $_GET['id']   ?? null;
$idCartao = $_POST['idCartao'] ?? null;
$cartao   = $_POST['cartao']   ?? [];

$Cartoes = new CartaoModel();
$retorno = null;

switch ($acao) {
    case 'adicionar':
        $retorno = $Cartoes->adicionaCartao($cartao);
        break;

    case 'alterar':
        $Cartoes->setId((int) $idCartao);
        $retorno = $Cartoes->alterarCartao($cartao);
        break;

    case 'busca':
        $retorno = $Cartoes->buscaCartaos();
        break;

    case 'excluir':
        $Cartoes->setId((int) $id);
        $retorno = $Cartoes->excluiCartao();
        break;

    case 'faturaPaga':
        $cid = (int) ($_POST['cartaoId'] ?? $_GET['cartaoId'] ?? 0);
        $mes = (int) ($_POST['mes'] ?? $_GET['mes'] ?? date('n'));
        $ano = (int) ($_POST['ano'] ?? $_GET['ano'] ?? date('Y'));
        $dataPago = CartaoModel::getFaturaPaga($cid, $mes, $ano);
        $retorno  = ['pago' => $dataPago !== null, 'data_pagamento' => $dataPago];
        break;

    case 'marcarFaturaPaga':
        $cid  = (int) ($_POST['cartaoId'] ?? 0);
        $mes  = (int) ($_POST['mes']  ?? date('n'));
        $ano  = (int) ($_POST['ano']  ?? date('Y'));
        $data = $_POST['data'] ?? date('Y-m-d');
        $pago = (bool) (int) ($_POST['pago'] ?? 1);
        $retorno = $pago
            ? CartaoModel::marcarFaturaPaga($cid, $mes, $ano, $data)
            : CartaoModel::desmarcarFaturaPaga($cid, $mes, $ano);
        break;

    default:
        http_response_code(400);
        $retorno = ['erro' => 'Ação inválida'];
        break;
}

echo json_encode($retorno);
