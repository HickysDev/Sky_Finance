<?php
include_once __DIR__ . '/../models/CategoriaModel.php';

header('Content-Type: application/json; charset=utf-8');

$acao      = $_POST['acao']      ?? $_GET['acao']      ?? '';
$id        = $_POST['id']        ?? $_GET['id']        ?? null;
$descricao = trim($_POST['descricao'] ?? '');
$nome      = trim($_POST['nome']      ?? '');
$cor       = trim($_POST['cor']       ?? '#6B7280');
$icone     = trim($_POST['icone']     ?? '');

$Categoria = new CategoriaModel();
$retorno   = null;

switch ($acao) {
    case 'adicionar':
        $Categoria->setDescricao($descricao);
        $Categoria->setCor($cor ?: '#6B7280');
        $Categoria->setIcone($icone ?: null);
        $retorno = $Categoria->adicionaCategoria();
        break;

    case 'busca':
        $retorno = $Categoria->buscaCategorias();
        break;

    case 'editar':
        $Categoria->setDescricao($nome);
        $Categoria->setId((int) $id);
        $Categoria->setCor($cor ?: '#6B7280');
        $Categoria->setIcone($icone ?: null);
        $retorno = $Categoria->editaCategoria();
        break;

    case 'excluir':
        $Categoria->setId((int) $id);
        $retorno = $Categoria->excluiCategoria();
        break;

    default:
        http_response_code(400);
        $retorno = ['erro' => 'Ação inválida'];
        break;
}

echo json_encode($retorno);
