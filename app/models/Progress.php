<?php
require_once 'Database.php';

class Progress
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getProgress($user_id, $video_id)
    {
        $stmt = $this->conn->prepare("
            SELECT progress FROM video_progress 
            WHERE user_id = ? AND video_id = ?
        ");
        $stmt->bind_param("ii", $user_id, $video_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result['progress'] ?? 0;
    }

    public function updateProgress($user_id, $video_id, $progress)
    {
        // Ambil progress terakhir
        $stmt = $this->conn->prepare("
        SELECT progress FROM video_progress WHERE user_id = ? AND video_id = ?
    ");
        $stmt->bind_param("ii", $user_id, $video_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result) {
            // Cek jika progress baru lebih kecil dari sebelumnya
            if ($progress <= $result['progress']) {
                return; // jangan update
            }

            // Update progress
            $stmt = $this->conn->prepare("
            UPDATE video_progress SET progress = ?, updated_at = NOW() 
            WHERE user_id = ? AND video_id = ?
        ");
            $stmt->bind_param("iii", $progress, $user_id, $video_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Insert baru
            $stmt = $this->conn->prepare("
            INSERT INTO video_progress (user_id, video_id, progress, updated_at) 
            VALUES (?, ?, ?, NOW())
        ");
            $stmt->bind_param("iii", $user_id, $video_id, $progress);
            $stmt->execute();
            $stmt->close();
        }
    }

    public function getPlaylistProgress($user_id, $playlist_id) {
    $stmt = $this->conn->prepare("
        SELECT AVG(progress) AS avg_progress
        FROM video_progress vp
        JOIN videos v ON v.id = vp.video_id
        WHERE vp.user_id = ? AND v.playlist_id = ?
    ");
    $stmt->bind_param("ii", $user_id, $playlist_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return round($result['avg_progress'] ?? 0);
}

}
