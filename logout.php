<?php
ini_set('session.cookie_httponly', '1');
session_start();

$_SESSION = [];
session_destroy();

require_once __DIR__ . '/conn/config.php';
header('Location: ' . BASE_URL . '/login.php');
exit;
