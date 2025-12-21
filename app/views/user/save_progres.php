<?php
session_start();
require_once __DIR__ . '/../models/Progress.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'NOT_LOGGED_IN';
    exit;
}

$user_id  = (int) $_SESSION['user_id'];
$video_id = (int) ($_POST['video_id'] ?? 0);
$progress = (int) ($_POST['progress'] ?? -1);

if ($video_id <= 0 || $progress < 0 || $progress > 100) {
    http_response_code(400);
    echo 'INVALID_DATA';
    exit;
}

$progressModel = new Progress();
$progressModel->updateProgress($user_id, $video_id, $progress);

echo 'OK';
