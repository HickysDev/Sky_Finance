<?php
include_once __DIR__ . '/../models/GastosModel.php';

header('Content-Type: application/json; charset=utf-8');

$acao     = $_POST['acao']     ?? $_GET['acao']     ?? '';
$mes      = $_POST['mes']      ?? $_GET['mes']      ?? null;
$ano      = (int) ($_POST['ano'] ?? $_GET['ano'] ?? date('Y'));
$cartaoId = $_POST['cartaoId'] ?? $_GET['cartaoId'] ?? null;
$tipo     = $_POST['tipo']     ?? $_GET['tipo']      ?? '';
$ids      = $_POST['ids']      ?? [];
$id       = $_POST['id']       ?? $_GET['id']       ?? null;
$descricao    = trim($_POST['descricao']    ?? '');
$valor        = $_POST['valor']        ?? '';
$categoria    = $_POST['categoria']    ?? null;
$metodo       = $_POST['metodo']       ?? null;
$cartao       = $_POST['cartao']       ?? null;
$data         = $_POST['data']         ?? null;
$parcelado    = $_POST['parcelado']    ?? null;
$num_parcelas = $_POST['num_parcelas'] ?? null;
$recorrente   = $_POST['recorrente']   ?? null;
$nome         = trim($_POST['nome']    ?? '');

$responsavel = isset($_POST['responsavel']) && $_POST['responsavel'] !== '' ? (int) $_POST['responsavel'] : null;

$retorno = null;

switch ($acao) {
    case 'adicionar':
        $valor = str_replace(['R$', ' ', '.'], '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valor = (float) $valor;

        if ($tipo === 'debito' || $tipo === 'recorrente') {
            $parcelado    = "N";
            $num_parcelas = null;
        }

        $retorno = GastosModel::adicionarGasto($descricao, $valor, $categoria, $metodo, $cartao, $data, $parcelado, $num_parcelas, $tipo, $recorrente, $responsavel);
        break;

    case 'buscar':
        $retorno = GastosModel::buscarGastosPorMes($mes, $ano, $cartaoId, $tipo);
        break;

    case 'remover':
        $retorno = GastosModel::excluirGastos($ids, $tipo);
        break;

    case 'buscarCredito':
        $retorno = GastosModel::buscarCredito($mes, $ano, $cartaoId);
        break;

    case 'buscarRecorrentes':
        $retorno = GastosModel::buscarRecorrentes($cartaoId ?? null);
        break;

    case 'buscaFatura':
        $retorno = GastosModel::buscarFatura($mes, $ano, $cartaoId);
        break;

    case 'inativaRecorrentes':
        $retorno = GastosModel::inativaRecorrentes($id);
        break;

    case 'ativaRecorrentes':
        $retorno = GastosModel::ativaRecorrentes($id);
        break;

    case 'reativarRecorrente':
        $dataReativacao = $_POST['data'] ?? $_GET['data'] ?? null;
        $retorno = GastosModel::reativarRecorrente($id, $dataReativacao);
        break;

    case 'editar':
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }
        $valorEdit = str_replace(['R$', ' ', '.'], '', $valor);
        $valorEdit = (float) str_replace(',', '.', $valorEdit);
        $retorno = GastosModel::editarGasto($id, $descricao, $valorEdit, $categoria, $metodo, $cartao, $data);
        break;

    case 'editarSimples':
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }
        $retorno = GastosModel::editarGastoSimples($id, $descricao, $categoria);
        break;

    case 'editarRecorrenteSimples':
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }
        $retorno = GastosModel::editarRecorrenteSimples($id, $descricao, $categoria);
        break;

    case 'editaGasto':
        if (!empty($valor)) {
            $valor = str_replace(['R$', ' ', '.'], '', $valor);
            $valor = str_replace(',', '.', $valor);
            $valor = (float) $valor;
        }
        $retorno = GastosModel::editaRecorrentes($id, $nome, $valor, $categoria, $cartao);
        break;

    case 'dashboard':
        $mesNum = (int) ($mes ?? date('n'));
        $retorno = GastosModel::buscarResumoMes($mesNum, $ano);
        break;

    case 'resumoAnual':
        $retorno = GastosModel::resumoAnual((int) ($ano ?? date('Y')));
        break;

    case 'gastosPorCategoria':
        $catNome = trim($_POST['categoria'] ?? '');
        $mesNum  = (int) ($mes ?? date('n'));
        if (!$catNome) { echo json_encode([]); exit; }
        $retorno = GastosModel::gastosPorCategoria($mesNum, $ano, $catNome);
        break;

    default:
        http_response_code(400);
        $retorno = ['erro' => 'Ação inválida'];
        break;
}

echo json_encode($retorno);
