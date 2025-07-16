<?php

include_once("../models/FinancasModel.php");

foreach ($_REQUEST as $key => $val) {
    ${$key} = $val;
}

$Financas = new FinancasModel();

switch ($acao) {

    case "adicionar":
        $Categoria->setDescricao($descricao);

        $retorno = $Categoria->adicionaCategoria();
        break;

    case "busca":
        $retorno = $Categoria->buscaCategorias();
        break;

    case "editar":
        $Categoria->setDescricao($nome);
        $Categoria->setId($id);

        $retorno = $Categoria->editaCategoria();
        break;

    case "excluir":
        $Categoria->setId($id);

        $retorno = $Categoria->excluiCategoria();
        break;    

    default:
        break;
}

echo json_encode($retorno);