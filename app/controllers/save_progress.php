<?php
session_start();
require_once __DIR__ . '/../models/Progress.php';

$user_id = $_SESSION['user_id'];
$video_id = $_POST['video_id'] ?? null;
$progress = $_POST['progress'] ?? null;

if ($video_id && $progress !== null) {
    $progressModel = new Progress();
    $progressModel->updateProgress($user_id, $video_id, $progress);
}

echo "OK";
