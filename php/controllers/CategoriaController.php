<?php

include_once("../models/CategoriaModel.php");

foreach ($_REQUEST as $key => $val) {
    ${$key} = $val;
}

$Categoria = new CategoriaModel();

switch ($acao) {

    case "adicionar":
        $Categoria->setDescricao($descricao);

        $retorno = $Categoria->adicionaCategoria();
        break;

    case "busca":
        $retorno = $Categoria->buscaCategorias();
        break;

    default:
        break;
}

echo json_encode($retorno);
