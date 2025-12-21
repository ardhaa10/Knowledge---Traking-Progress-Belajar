<?php
session_start();
require_once __DIR__ . '/../models/Playlist.php';

class PlaylistController {
    private $playlistModel;

    public function __construct() {
        $this->playlistModel = new Playlist();
    }

    public function index() {
        return $this->playlistModel->getAllPlaylists();
    }

    public function store($data) {
        $this->playlistModel->addPlaylist($data['name'], $data['description'] ?? '');
        header("Location: ../../app/views/admin/playlist_management.php");
        exit;
    }

    public function update($data) {
        $this->playlistModel->updatePlaylist($data['id'], $data['name'], $data['description'] ?? '');
        header("Location: ../../app/views/admin/playlist_management.php");
        exit;
    }

    public function destroy($id) {
        $this->playlistModel->deletePlaylist($id);
        header("Location: ../../app/views/admin/playlist_management.php");
        exit;
    }
}
?>
