<?php

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    $redir = defined('BASE_URL') ? BASE_URL . '/login.php' : '/login.php';
    header('Location: ' . $redir);
    exit;
}
