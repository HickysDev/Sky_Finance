<?php

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    // Controllers AJAX precisam de 401 + JSON: redirecionar devolveria o HTML do
    // login com status 200, e o front trataria isso como resposta válida.
    // jQuery envia X-Requested-With em todo $.ajax, inclusive nos uploads FormData.
    $ehAjax = strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') === 0;

    if ($ehAjax) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => 'Sessão expirada. Faça login novamente.']);
        exit;
    }

    $redir = defined('BASE_URL') ? BASE_URL . '/login.php' : '/login.php';
    header('Location: ' . $redir);
    exit;
}
