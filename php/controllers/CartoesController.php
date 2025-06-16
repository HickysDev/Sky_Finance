<?php

include_once("../models/CartoesModel.php");

foreach ($_REQUEST as $key => $val) {
    ${$key} = $val;
}

$Cartoes = new CartaoModel();

switch ($acao) {

    case "adicionar":

        $retorno = $Cartoes->adicionaCartao($cartao);
        break;

    case "alterar":
        $Cartoes->setId($idCartao);
        $retorno = $Cartoes->alterarCartao($cartao);
        break;    

    case "busca":
        $retorno = $Cartoes->buscaCartaos();
        break;

    case "excluir":
        $Cartoes->setId($id);
        $retorno = $Cartoes->excluiCartao();
        break;

    default:
        break;
}

echo json_encode($retorno);
