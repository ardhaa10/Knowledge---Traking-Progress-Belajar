<?php
require_once __DIR__ . '/../models/Video.php';

class VideoController {
    private $videoModel;

    public function __construct() {
        $this->videoModel = new Video();
    }

    public function index() {
        return $this->videoModel->getAllVideos();
    }

    public function store($data) {
        $this->videoModel->addVideo(
            $data['youtube_video_id'],
            $data['title'],
            $data['description'] ?? '',
            $data['thumbnail'] ?? '',
            $data['playlist_id']
        );
        header("Location: ../../app/views/admin/content_management.php");
        exit;
    }

    public function destroy($id) {
        $this->videoModel->deleteVideo($id);
        header("Location: ../../app/views/admin/content_management.php");
        exit;
    }
}
?>
