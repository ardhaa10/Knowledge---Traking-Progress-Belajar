<?php
require_once 'PlaylistController.php';

if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../views/auth/login.php");
    exit;
}

$controller = new PlaylistController();
$action = $_GET['action'] ?? '';

if ($action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->store($_POST);
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->update($_POST);
}

if ($action === 'delete') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $controller->destroy($id);
    }
}
?>
