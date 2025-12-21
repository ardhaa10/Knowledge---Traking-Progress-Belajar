<?php
require_once 'Database.php';

class Video
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllVideos()
    {
        $stmt = $this->conn->prepare("
            SELECT v.*, p.name as playlist_name 
            FROM videos v
            JOIN playlists p ON v.playlist_id = p.id
            ORDER BY v.created_at DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    public function addVideo($youtube_video_id, $title, $description, $thumbnail_url, $playlist_id)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO videos (youtube_video_id, title, description, thumbnail_url, playlist_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssi", $youtube_video_id, $title, $description, $thumbnail_url, $playlist_id);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    public function deleteVideo($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM videos WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    public function getVideoById($id)
    {
        $stmt = $this->conn->prepare("
        SELECT v.*, p.name as playlist_name
        FROM videos v
        JOIN playlists p ON v.playlist_id = p.id
        WHERE v.id = ?
        LIMIT 1
    ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    public function getVideosByPlaylist($playlist_id)
    {
        $stmt = $this->conn->prepare("
        SELECT id, title, thumbnail_url 
        FROM videos
        WHERE playlist_id = ?
        ORDER BY id DESC
    ");
        $stmt->bind_param("i", $playlist_id);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }
}
