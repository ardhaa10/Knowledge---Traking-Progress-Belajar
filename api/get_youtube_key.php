<?php
session_start();
require_once __DIR__ . '/../config/env.php';

// Validasi bahwa user adalah admin
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['youtube_api_key' => getenv('YOUTUBE_API_KEY')]);