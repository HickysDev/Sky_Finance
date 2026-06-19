<?php

include_once __DIR__ . '/../models/ResponsaveisModel.php';
include_once __DIR__ . '/../models/ContasPessoaModel.php';

header('Content-Type: application/json; charset=utf-8');

$acao      = $_POST['acao']      ?? $_GET['acao']      ?? '';
$id        = isset($_POST['id'])        ? (int)   $_POST['id']        : 0;
$nome      = trim($_POST['nome']        ?? '');
$cor       = $_POST['cor']              ?? '#3B82F6';
$mes       = (int) ($_POST['mes']       ?? $_GET['mes']  ?? date('n'));
$ano       = (int) ($_POST['ano']       ?? $_GET['ano']  ?? date('Y'));
$descricao = trim($_POST['descricao']   ?? '');
$data      = $_POST['data']             ?? date('Y-m-d');
$pago      = isset($_POST['pago'])      ? (bool) (int) $_POST['pago'] : false;

$valor = 0.0;
if (!empty($_POST['valor'])) {
    $v = str_replace(['R$', ' ', '.'], '', $_POST['valor']);
    $valor = (float) str_replace(',', '.', $v);
}

$categoriaId     = isset($_POST['categoria']) && $_POST['categoria'] !== '' ? (int) $_POST['categoria'] : null;
$metodoPagamento = trim($_POST['metodo_pagamento'] ?? 'Dinheiro') ?: 'Dinheiro';

$retorno = null;

switch ($acao) {
    // ── Responsáveis ─────────────────────────────────────────────
    case 'buscar':
        $retorno = ResponsaveisModel::buscar();
        break;

    case 'adicionar':
        if (!$nome) { http_response_code(400); echo json_encode(['erro' => 'Nome obrigatório']); exit; }
        $retorno = ResponsaveisModel::adicionar($nome, $cor);
        break;

    case 'editar':
        if (!$id || !$nome) { http_response_code(400); echo json_encode(['erro' => 'Dados inválidos']); exit; }
        $retorno = ResponsaveisModel::editar($id, $nome, $cor);
        break;

    case 'excluir':
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }
        $retorno = ResponsaveisModel::excluir($id);
        break;

    case 'resumo':
        $retorno = ResponsaveisModel::resumo($mes, $ano);
        break;

    case 'despesas':
        $retorno = ResponsaveisModel::despesas($id, $mes, $ano);
        break;

    // ── Contas por pessoa ─────────────────────────────────────────
    case 'contas.resumo':
        $retorno = ContasPessoaModel::resumo($mes, $ano);
        break;

    case 'contas.medeve':
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }
        $retorno = ContasPessoaModel::despesasMeDeve($id, $mes, $ano);
        break;

    case 'contas.listar':
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }
        $mesFiltro = isset($_POST['mes']) && $_POST['mes'] !== '' ? (int) $_POST['mes'] : null;
        $anoFiltro = isset($_POST['ano']) && $_POST['ano'] !== '' ? (int) $_POST['ano'] : null;
        $retorno = ContasPessoaModel::listar($id, $mesFiltro, $anoFiltro);
        break;

    case 'contas.adicionar':
        if (!$id || !$descricao || $valor <= 0) {
            http_response_code(400); echo json_encode(['erro' => 'Dados inválidos']); exit;
        }
        $retorno = ContasPessoaModel::adicionar($id, $descricao, $valor, $data, $categoriaId, $metodoPagamento);
        break;

    case 'contas.pago':
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }
        $retorno = ContasPessoaModel::marcarPago($id, $pago);
        break;

    case 'contas.remover':
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }
        $retorno = ContasPessoaModel::remover($id);
        break;

    default:
        http_response_code(400);
        $retorno = ['erro' => 'Ação inválida'];
        break;
}

echo json_encode($retorno);
