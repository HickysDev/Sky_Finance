<?php

// ── Base URL auto-detectada (funciona mesmo se renomear/mover a pasta) ──────
$_base = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$_root = str_replace('\\', '/', realpath(isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : ''));
define('BASE_URL',  ($_root !== '' && strpos($_base, $_root) === 0)
    ? str_replace($_root, '', $_base)
    : '/Sky_Finance');
define('BASE_PATH', $_base);      // caminho absoluto no filesystem
unset($_base, $_root);

// ── URLs dos controllers (absolutas, funcionam de qualquer página) ───────────
define('CTRL_GASTOS',    BASE_URL . '/php/controllers/GastosController.php');
define('CTRL_CATEGORIA', BASE_URL . '/php/controllers/CategoriaController.php');
define('CTRL_CARTOES',   BASE_URL . '/php/controllers/CartoesController.php');
define('CTRL_FINANCAS',   BASE_URL . '/php/controllers/FinancasController.php');
define('CTRL_ORCAMENTO',  BASE_URL . '/php/controllers/OrcamentoController.php');
define('CTRL_COFRINHO',      BASE_URL . '/php/controllers/CofrinhoController.php');
define('CTRL_RESPONSAVEIS',  BASE_URL . '/php/controllers/ResponsaveisController.php');
define('CTRL_CONTAS_FIXAS',  BASE_URL . '/php/controllers/ContasFixasController.php');
define('CTRL_USUARIOS',      BASE_URL . '/php/controllers/UsuariosController.php');

// ── Caminho base para includes PHP (use em qualquer nível de pasta) ──────────
// Exemplo: require_once BASE_PATH . '/php/templates/header.php';
