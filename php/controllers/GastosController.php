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
        $valor = (float) $valor;

        if ($tipo == 'debito') {
            $parcelado = "N";
            $num_parcelas = "";
        } else if ($tipo == 'recorrente') {
            $parcelado = "N";
            $num_parcelas = "";
        }

        $retorno = GastosModel::adicionarGasto($descricao, $valor, $categoria, $metodo, $cartao, $data, $parcelado, $num_parcelas, $tipo, $recorrente);

        break;

    case 'buscar':
        $retorno = GastosModel::buscarGastosPorMes($mes, $cartaoId, $tipo);
        break;

    case 'remover':
        $retorno = GastosModel::excluirGastos($ids, $tipo);
        break;

    case 'buscarCredito':
        $retorno = GastosModel::buscarCredito($mes, $cartaoId);
        break;

    case 'buscarRecorrentes':
        $retorno = GastosModel::buscarRecorrentes($cartaoId ?? NULL);
        break;

    case "buscaFatura":
        $retorno = GastosModel::buscarFatura($mes, $cartaoId);
        break;

    case "inativaRecorrentes":
        $retorno = GastosModel::inativaRecorrentes($id);
        break;

    case "ativaRecorrentes":
        $retorno = GastosModel::ativaRecorrentes($id);
        break;

    case "editaGasto":
        if (!empty($valor)) {
            // Se vier com "R$", trata como moeda brasileira
            if (strpos($valor, 'R$') !== false || strpos($valor, ',') !== false) {
                $valor = str_replace(['R$', ' ', '.'], '', $valor); // Remove símbolos e pontos
                $valor = str_replace(',', '.', $valor);             // Converte vírgula para ponto
            }

            $valor = (float) $valor;
        }

        $retorno = GastosModel::editaRecorrentes($id, $nome, $valor, $categoria, $cartao);
        break;

    default:
        break;
}

echo json_encode($retorno);
