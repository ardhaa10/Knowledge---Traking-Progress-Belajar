<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../models/Video.php';
require_once __DIR__ . '/../../models/Progress.php';
require_once __DIR__ . '/../../models/Playlist.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . url('login'));
    exit;
}

$user_id = $_SESSION['user_id'];
$video_id = $_GET['id'] ?? null;

if (!$video_id) die("Video tidak ditemukan");

$videoModel = new Video();
$progressModel = new Progress();
$playlistModel = new Playlist();

$video = $videoModel->getVideoById($video_id);
if (!$video) die("Video tidak tersedia");

// Playlist ID diambil dari video
$playlist_id = $video['playlist_id'];

// Ambil list video dalam playlist yang sama
$videos = $videoModel->getVideosByPlaylist($playlist_id);

// Progress video aktif
$lastProgress = $progressModel->getProgress($user_id, $video_id);

// Nama Playlist
$playlistName = "Playlist";
$p = $playlistModel->getPlaylistById($playlist_id);
if ($p) $playlistName = $p['name'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($video['title']) ?> - MyCourse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Navbar Styling */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
            padding: 1rem 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-nav .nav-link {
            color: #475569 !important;
            font-weight: 500;
            padding: 0.5rem 1.25rem !important;
            margin: 0 0.25rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea !important;
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .navbar-toggler {
            border: 2px solid #667eea;
            padding: 0.5rem 0.75rem;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23667eea' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }

        /* Main Container */
        .main-container {
            padding: 2rem 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Video Container */
        .video-container {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            margin-bottom: 1.5rem;
        }

        .video-container h4 {
            font-weight: 700;
            font-size: 1.75rem;
            color: #1e293b;
            margin-bottom: 1.5rem;
            line-height: 1.4;
        }

        .ratio {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .progress-wrapper {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .progress-label span:first-child {
            font-weight: 600;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .progress-label span:last-child {
            font-weight: 700;
            font-size: 1.25rem;
            color: #667eea;
        }

        .progress {
            height: 24px;
            background: #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .progress-bar {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            transition: width 0.3s ease;
        }

        /* Playlist Sidebar */
        .playlist-sidebar {
            background: #fff;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-height: calc(100vh - 150px);
            overflow-y: auto;
            position: sticky;
            top: 100px;
        }

        .playlist-sidebar h5 {
            font-weight: 700;
            font-size: 1.25rem;
            color: #1e293b;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .playlist-sidebar h5 i {
            color: #667eea;
        }

        /* Video List Card */
        .video-list-card {
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8fafc;
            border: 2px solid transparent;
        }

        .video-list-card:hover {
            background: #f1f5f9;
            transform: translateX(4px);
            border-color: #e2e8f0;
        }

        .video-active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            font-weight: 600;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .video-active:hover {
            transform: translateX(4px);
            border-color: transparent;
        }

        .video-active .video-icon {
            color: #fff !important;
        }

        .video-active .video-progress-text {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .video-list-card .video-icon {
            font-size: 1.125rem;
            transition: all 0.3s ease;
        }

        .video-list-card .video-title {
            font-size: 0.9rem;
            font-weight: 500;
            color: #334155;
            line-height: 1.4;
        }

        .video-active .video-title {
            color: #fff;
        }

        .video-progress-text {
            font-size: 0.875rem;
            font-weight: 600;
            color: #667eea;
        }

        .video-list-card .progress {
            height: 6px;
            background: rgba(226, 232, 240, 0.5);
            margin-top: 0.75rem;
        }

        .video-active .progress {
            background: rgba(255, 255, 255, 0.3);
        }

        .video-list-card .progress-bar {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }

        .video-active .progress-bar {
            background: #fff;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 20px;
            border: none;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 1.5rem;
        }

        .modal-header .modal-title {
            font-weight: 700;
            font-size: 1.25rem;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 2rem;
            font-size: 1rem;
            color: #475569;
            line-height: 1.6;
        }

        .modal-footer {
            border: none;
            padding: 1rem 2rem 2rem;
        }

        .modal-footer .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            border: none;
        }

        .modal-footer .btn-secondary {
            background: #94a3b8;
        }

        .modal-footer .btn-secondary:hover {
            background: #64748b;
        }

        .modal-footer .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .modal-footer .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .video-container {
                padding: 1.25rem;
            }

            .video-container h4 {
                font-size: 1.25rem;
            }

            .playlist-sidebar {
                margin-top: 1.5rem;
                max-height: none;
                position: relative;
                top: 0;
            }

            .progress-wrapper {
                padding: 1rem;
            }
        }

        /* Custom scrollbar */
        .playlist-sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .playlist-sidebar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .playlist-sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .playlist-sidebar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Knowladge</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a href="<?= url('/dashboard') ?>" class="nav-link">Dashboard</a></li>
                <li class="nav-item"><a href="<?= url('/jadwal') ?>" class="nav-link">Jadwal</a></li>
                <li class="nav-item"><a href="<?= url('/kelas') ?>" class="nav-link active">Kelas</a></li>
                <li class="nav-item"><a href="<?= url('/profile') ?>" class="nav-link">Profile</a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>/app/controllers/auth_action.php?action=logout" class="nav-link">Logout</a>
            </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="row">

            <!-- LEFT VIDEO PLAYER -->
            <div class="col-lg-9 col-md-8">
                <div class="video-container">
                    <h4><?= htmlspecialchars($video['title']) ?></h4>

                    <div class="ratio ratio-16x9">
                        <iframe id="courseVideo"
                            src="https://www.youtube.com/embed/<?= $video['youtube_video_id'] ?>?enablejsapi=1"
                            title="Course Video"
                            allowfullscreen></iframe>
                    </div>

                    <div class="progress-wrapper">
                        <div class="progress-label">
                            <span>
                                <i class="fa-solid fa-chart-line"></i>
                                Progress Pembelajaran
                            </span>
                            <span id="progressText"><?= $lastProgress ?>%</span>
                        </div>
                        <div class="progress">
                            <div id="progressBar"
                                class="progress-bar"
                                style="width: <?= $lastProgress ?>%">
                                <?= $lastProgress ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT LIST VIDEO -->
            <div class="col-lg-3 col-md-4">
                <div class="playlist-sidebar">
                    <h5>
                        <i class="fa-solid fa-list-ul"></i>
                        <?= $playlistName ?>
                    </h5>

                    <?php foreach ($videos as $v):
                        $p = $progressModel->getProgress($user_id, $v['id']);
                        $isActive = ($v['id'] == $video_id);
                    ?>
                        <a href="<?= url('video/' . $v['id']) ?>" style="text-decoration:none;">
                            <div class="video-list-card <?= $isActive ? 'video-active' : '' ?>">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="d-flex align-items-start gap-2 flex-grow-1">
                                        <?php if ($p == 100): ?>
                                            <i class="fa-solid fa-circle-check text-success video-icon mt-1"></i>
                                        <?php elseif ($isActive): ?>
                                            <i class="fa-solid fa-play-circle text-warning video-icon mt-1"></i>
                                        <?php else: ?>
                                            <i class="fa-regular fa-circle text-secondary video-icon mt-1"></i>
                                        <?php endif; ?>
                                        <span class="video-title"><?= htmlspecialchars($v['title']) ?></span>
                                    </div>
                                    <small class="video-progress-text"><?= $p ?>%</small>
                                </div>

                                <div class="progress">
                                    <div class="progress-bar <?= $p == 100 ? 'bg-success' : '' ?>"
                                        style="width: <?= $p ?>%"></div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach ?>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL -->
    <?php if ($lastProgress > 0 && $lastProgress < 100): ?>
        <div class="modal fade" id="resumeModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">
                            <i class="fa-solid fa-play-circle me-2"></i>
                            Lanjutkan Menonton?
                        </h6>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <i class="fa-solid fa-info-circle text-primary me-2"></i>
                        Kamu sudah menonton hingga <strong><?= $lastProgress ?>%</strong>. <br>
                        Lanjutkan dari bagian terakhir?
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" id="startOverBtn">
                            <i class="fa-solid fa-rotate-left me-2"></i>
                            Mulai Dari Awal
                        </button>
                        <button class="btn btn-primary" id="resumeBtn">
                            <i class="fa-solid fa-forward me-2"></i>
                            Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- YouTube API -->
    <script src="https://www.youtube.com/iframe_api"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let player;
        let savedProgress = <?= $lastProgress ?>;
        let intervalId;
        let resumeFromTime = 0;
        const progressBar = document.getElementById("progressBar");
        const progressText = document.getElementById("progressText");

        function onYouTubeIframeAPIReady() {
            player = new YT.Player("courseVideo", {
                events: {
                    onReady: onPlayerReady,
                    onStateChange: onPlayerStateChange
                }
            });
        }

        function onPlayerReady() {
            if (savedProgress > 0 && savedProgress < 100) {
                const modal = new bootstrap.Modal(document.getElementById("resumeModal"));
                modal.show();

                document.getElementById("resumeBtn").onclick = function() {
                    const duration = player.getDuration();
                    resumeFromTime = Math.floor((savedProgress / 100) * duration);
                    player.seekTo(resumeFromTime, true);
                    modal.hide();
                };

                document.getElementById("startOverBtn").onclick = function() {
                    player.seekTo(0, true);
                    modal.hide();
                };
            }
        }

        function onPlayerStateChange(event) {
            if (event.data === YT.PlayerState.PLAYING) startTrack();
            if (event.data === YT.PlayerState.PAUSED) clearInterval(intervalId);
            if (event.data === YT.PlayerState.ENDED) {
                clearInterval(intervalId);
                saveProgress(100);
                progressBar.style.width = "100%";
                progressBar.innerText = "100%";
                progressText.innerText = "100%";
            }
        }

        function startTrack() {
    clearInterval(intervalId);

    intervalId = setInterval(() => {
        const duration = player.getDuration();
        if (!duration || duration <= 0) return;

        const percent = Math.floor(
            (player.getCurrentTime() / duration) * 100
        );

        if (percent < 0 || percent > 100) return;

        progressBar.style.width = percent + "%";
        progressBar.innerText = percent + "%";
        progressText.innerText = percent + "%";

        // simpan tiap naik minimal 5%
        if (percent >= savedProgress + 5) {
            savedProgress = percent;
            saveProgress(percent);
        }
    }, 1000);
}

        function saveProgress(x) {
    fetch("<?= BASE_URL ?>/app/controllers/save_progress.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `video_id=<?= $video_id ?>&progress=${x}`
    });
}
    </script>

</body>

</html>