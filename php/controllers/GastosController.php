<?php
include_once("../models/GastosModel.php");

foreach ($_REQUEST as $key => $val) {
    ${$key} = $val;
}

switch ($acao) {
    case 'adicionar':

        //TRATANDO O VALOR
        $valor = str_replace(['R$', ' ', '.'], '', $valor); // Remove "R$" e espaços
        $valor = str_replace(',', '.', $valor);   // Substitui a vírgula por ponto

        //Converte para float
        $valor = (float)$valor;

        if($tipo == 'debito'){
            $parcelado = "N";
            $num_parcelas = "";
        }

        $retorno = GastosModel::adicionarGasto($descricao, $valor, $categoria, $metodo, $cartao, $data, $parcelado, $num_parcelas, $tipo);

        break;

    case 'buscar':
        $retorno = GastosModel::buscarGastosPorMes($mes, $cartaoId, $tipo);
        break;

    case 'remover':
        $retorno = GastosModel::excluirGastos($ids, $tipo);
        break;

    case 'buscarRecorrente':
        $retorno = GastosModel::buscarRecorrentes($mes);
        break;
    default:
        break;
}

echo json_encode($retorno);
