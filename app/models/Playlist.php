<?php
require_once 'Database.php';

class Playlist {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllPlaylists() {
        $stmt = $this->conn->prepare("SELECT * FROM playlists ORDER BY created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    public function getPlaylistById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM playlists WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $playlist = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $playlist;
    }

    public function addPlaylist($name, $description) {
        $stmt = $this->conn->prepare("INSERT INTO playlists (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    public function updatePlaylist($id, $name, $description) {
        $stmt = $this->conn->prepare("UPDATE playlists SET name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $description, $id);
        $stmt->execute();
        $stmt->close();
    }

    public function deletePlaylist($id) {
        $stmt = $this->conn->prepare("DELETE FROM playlists WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}
?>
