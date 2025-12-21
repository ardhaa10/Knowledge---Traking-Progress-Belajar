<?php
session_start();
require_once __DIR__ . '/UserController.php';

// Pastikan cuma admin - ABSOLUTE URL
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: /belajar-online/app/views/auth/login.php");
    exit;
}

$controller = new UserController();
$action = $_GET['action'] ?? '';

try {
    switch($action) {
        case 'store':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->store($_POST);
            }
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->update($_POST);
            }
            break;

        case 'delete':
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller->destroy($id);
            }
            break;

        default:
            header("Location: /belajar-online/app/views/admin/user_management.php");  // ABSOLUTE!
            exit;
    }
} catch (PDOException $e) {
    $error = urlencode($e->getMessage());
    header("Location: /belajar-online/app/views/admin/user_management.php?error=$error");
    exit;
}
?>
