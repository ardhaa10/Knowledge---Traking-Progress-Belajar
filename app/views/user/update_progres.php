<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$video_id = $_POST['video_id'] ?? 0;

if($video_id){
    $conn->query("
        UPDATE user_video_progress 
        SET status='done', last_watched=NOW() 
        WHERE user_id=$user_id AND video_id=$video_id
    ");
}
