<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/AuthController.php';

$controller = new AuthController();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login($_POST);
        }
        break;
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->register($_POST);
        }
        break;
    case 'logout':
        $controller->logout();
        break;
    default:
        header("Location: /belajar-online/login");
        exit;
}
?>
