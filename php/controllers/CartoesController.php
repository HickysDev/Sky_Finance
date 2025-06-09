<?php

include_once("../models/CartoesModel.php");

foreach ($_REQUEST as $key => $val) {
    ${$key} = $val;
}

$Cartoes = new CartaoModel();

switch ($acao) {

    case "adicionar":
        $Cartoes->setDescricao($descricao);

        $retorno = $Cartoes->adicionaCategoria();
        break;

    case "busca":
        $retorno = $Cartoes->buscaCartaos();
        break;

    default:
        break;
}

echo json_encode($retorno);
