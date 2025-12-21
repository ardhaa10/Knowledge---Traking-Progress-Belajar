<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../models/Video.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../models/Progress.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . url('login'));
    exit;
}

$user_id = $_SESSION['user_id'];

$videoModel = new Video();
$playlistModel = new Playlist();
$progressModel = new Progress();

// ambil semua playlist
$playlists = $playlistModel->getAllPlaylists();

// jika belum pilih playlist di URL, otomatis pakai playlist pertama
$playlist_id = $_GET['playlist_id'] ?? ($playlists[0]['id'] ?? null);

$videos = $videoModel->getVideosByPlaylist($playlist_id);

$selectedPlaylist = $playlistModel->getPlaylistById($playlist_id);
$playlist_name = $selectedPlaylist['name'] ?? "Playlist";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - MyCourse</title>
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
            background: #f8fafc;
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

        /* Sidebar Styling */
        .sidebar {
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            min-height: calc(100vh - 76px);
            padding: 2rem 1.5rem;
            color: #fff;
        }

        .sidebar h5 {
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: #fff;
        }

        .sidebar-card {
            cursor: pointer;
            padding: 1.25rem;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            margin-bottom: 1rem;
        }

        .sidebar-card:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(8px);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .sidebar-active {
            background: #fff !important;
            color: #1e293b !important;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            transform: translateX(8px);
        }

        .sidebar-active .playlist-name {
            color: #1e293b !important;
        }

        .sidebar-active .progress-label {
            color: #667eea !important;
        }

        .playlist-name {
            font-weight: 600;
            font-size: 1rem;
            color: #fff;
        }

        .progress-label {
            font-size: 0.875rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
        }

        .sidebar-card .progress {
            height: 6px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            overflow: hidden;
        }

        .sidebar-card .progress-bar {
            border-radius: 10px;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
        }

        .sidebar-active .progress {
            background: rgba(102, 126, 234, 0.2);
        }

        .sidebar-active .progress-bar {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }

        /* Main Content */
        .main-content {
            padding: 2.5rem 3rem;
            min-height: calc(100vh - 76px);
        }

        .content-header {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .content-header h3 {
            font-weight: 700;
            font-size: 2rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .content-header h3 i {
            color: #667eea;
        }

        /* Video Card */
        .video-card {
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .video-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.2);
        }

        .video-card img {
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .video-card:hover img {
            transform: scale(1.05);
        }

        .video-card .card-body {
            padding: 1.5rem;
        }

        .video-card h6 {
            font-weight: 600;
            font-size: 1.125rem;
            color: #1e293b;
            margin-bottom: 0.75rem;
            line-height: 1.4;
            min-height: 3em;
        }

        .video-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .badge-secondary {
            background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
            color: #fff;
        }

        .badge-warning {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #fff;
        }

        .badge-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
        }

        .progress-section {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .progress-section .progress {
            height: 8px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-section .progress-bar {
            border-radius: 10px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }

        .progress-section .progress-bar.bg-success {
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
        }

        .btn-play {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-play:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
            color: #fff;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                padding: 1.5rem 1rem;
            }

            .main-content {
                padding: 1.5rem 1rem;
            }

            .content-header h3 {
                font-size: 1.5rem;
            }

            .video-card img {
                height: 160px;
            }

            .sidebar-card:hover,
            .sidebar-active {
                transform: translateX(0);
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Knowledge</a>
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

    <div class="container-fluid">
        <div class="row">

            <!-- SIDEBAR PLAYLIST -->
            <div class="col-lg-3 col-md-4 p-0">
                <div class="sidebar">
                    <h5><i class="fa-solid fa-layer-group me-2"></i>Playlist Course</h5>

                    <?php foreach ($playlists as $playlist):
                        $playlistProgress = $progressModel->getPlaylistProgress($user_id, $playlist['id']);
                    ?>
                        <a href="<?= url('kelas?playlist_id=' . $playlist['id']) ?>" style="text-decoration:none;">"
                            <div class="sidebar-card <?= $playlist_id == $playlist['id'] ? 'sidebar-active' : '' ?>">

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="playlist-name"><?= htmlspecialchars($playlist['name']) ?></span>
                                    <span class="progress-label"><?= $playlistProgress ?>%</span>
                                </div>

                                <div class="progress">
                                    <div class="progress-bar <?= $playlistProgress == 100 ? 'bg-success' : '' ?>"
                                        style="width: <?= $playlistProgress ?>%">
                                    </div>
                                </div>

                            </div>
                        </a>
                    <?php endforeach ?>
                </div>
            </div>

            <!-- VIDEO LIST -->
            <div class="col-lg-9 col-md-8 p-0">
                <div class="main-content">
                    <div class="content-header">
                        <h3>
                            <i class="fa-solid fa-book-open"></i>
                            <?= $playlist_name ?>
                        </h3>
                    </div>

                    <?php if (empty($videos)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-video-slash"></i>
                            <h5>Belum Ada Video</h5>
                            <p>Playlist ini belum memiliki video</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($videos as $video):
                                $progress = $progressModel->getProgress($user_id, $video['id']);
                            ?>
                                <div class="col-xl-4 col-lg-6 col-md-12">
                                    <div class="card video-card">
                                        <img src="<?= htmlspecialchars($video['thumbnail_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($video['title']) ?>">

                                        <div class="card-body">
                                            <h6><?= htmlspecialchars($video['title']) ?></h6>

                                            <?php if ($progress == 0): ?>
                                                <span class="video-badge badge-secondary">
                                                    <i class="fa-solid fa-circle-play"></i>
                                                    Belum diputar
                                                </span>
                                            <?php elseif ($progress < 100): ?>
                                                <span class="video-badge badge-warning">
                                                    <i class="fa-solid fa-hourglass-half"></i>
                                                    Sedang dipelajari
                                                </span>
                                            <?php else: ?>
                                                <span class="video-badge badge-success">
                                                    <i class="fa-solid fa-circle-check"></i>
                                                    Selesai
                                                </span>
                                            <?php endif; ?>

                                            <div class="progress-section">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="progress-label" style="color: #64748b;">Progress</small>
                                                    <small class="progress-label" style="color: #667eea;"><?= $progress ?>%</small>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar <?= $progress == 100 ? 'bg-success' : '' ?>"
                                                        style="width: <?= $progress ?>%">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- HARUS INI -->
                                                <a href="<?= url('video/' . $video['id']) ?>" class="btn btn-play w-100 mt-3">
                                                <i class="fa-solid fa-play"></i>
                                                Play Video
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>