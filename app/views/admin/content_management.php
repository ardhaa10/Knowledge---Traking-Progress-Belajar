<?php
require_once __DIR__ . '/../../controllers/VideoController.php';
require_once __DIR__ . '/../../controllers/PlaylistController.php';
require_once __DIR__ . '/../../../config/env.php';

if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../views/auth/login.php");
    exit;
}

$videoController = new VideoController();
$playlistController = new PlaylistController();

$videos = $videoController->index();
$playlists = $playlistController->index();

// Ambil YouTube API Key dari environment
$youtubeApiKey = getenv('YOUTUBE_API_KEY');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Management - MyCourse</title>
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

        /* Sidebar Styling */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: #fff;
            padding: 2rem 1.5rem;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
        }

        .sidebar h4 {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.7);
            padding: 0.875rem 1.25rem;
            margin-bottom: 0.5rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateX(4px);
        }

        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Content Area */
        .content {
            padding: 2.5rem;
            min-height: 100vh;
        }

        .content-header {
            background: #fff;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .content-header h2 {
            font-weight: 700;
            font-size: 2rem;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .content-header h2 i {
            color: #667eea;
        }

        /* Search Section */
        .search-section {
            background: #fff;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .search-section h5 {
            font-weight: 600;
            font-size: 1.125rem;
            color: #1e293b;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-section h5 i {
            color: #667eea;
        }

        .search-input-group {
            position: relative;
            max-width: 600px;
        }

        .search-input-group input {
            height: 52px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 0 1.5rem 0 3.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .search-input-group i {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            z-index: 10;
        }

        .search-input-group button {
            position: absolute;
            right: 6px;
            top: 6px;
            height: 40px;
            padding: 0 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .search-input-group button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        /* Results Section */
        .results-section {
            background: #fff;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            min-height: 200px;
        }

        .results-section.empty {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
        }

        /* Video Cards */
        .video-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .video-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.2);
        }

        .video-card img {
            height: 180px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .video-card:hover img {
            transform: scale(1.05);
        }

        .video-card .card-body {
            padding: 1.25rem;
        }

        .video-card .card-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.4;
            height: 2.8em;
            overflow: hidden;
            margin-bottom: 0.75rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .video-card .card-text {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.4;
            height: 2.8em;
            overflow: hidden;
            margin-bottom: 0.75rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .playlist-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .btn-add {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 0.625rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
            color: #fff;
        }

        .btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 0.5rem;
            border-radius: 10px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            color: #fff;
        }

        /* Saved Videos Section */
        .saved-section {
            background: #fff;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .saved-section h4 {
            font-weight: 700;
            font-size: 1.5rem;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .saved-section h4 i {
            color: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }

        .empty-state p {
            font-size: 1rem;
            margin: 0;
        }

        /* Loading State */
        .loading {
            text-align: center;
            padding: 2rem;
            color: #667eea;
        }

        .loading i {
            font-size: 2rem;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .content {
                padding: 1.5rem 1rem;
            }

            .content-header,
            .search-section,
            .results-section,
            .saved-section {
                padding: 1.5rem;
            }

            .content-header h2 {
                font-size: 1.5rem;
            }

            .video-card img {
                height: 150px;
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
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar flex-shrink-0" style="width: 280px;">
            <h4><i class="fa-solid fa-shield-halved me-2"></i>Admin Panel</h4>
            <ul class="nav nav-pills flex-column mb-auto">
                <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i>Dashboard</a></li>
                <li><a href="user_management.php" class="nav-link"><i class="fa-solid fa-users"></i>Manage Users</a></li>
                <li><a href="playlist_management.php" class="nav-link"><i class="fa-solid fa-layer-group"></i>Manage Playlist</a></li>
                <li><a href="content_management.php" class="nav-link active"><i class="fa-solid fa-film"></i>Manage Content</a></li>
                <li><a href="jadwal_management.php" class="nav-link"><i class="fa-solid fa-calendar"></i>Manage Jadwal</a></li>
                <li><a href="reports.php" class="nav-link"><i class="fa-solid fa-chart-bar"></i>Reports</a></li>
                <li><a href="../../controllers/auth_action.php?action=logout" class="nav-link"><i class="fa-solid fa-right-from-bracket"></i>Logout</a></li>
            </ul>
        </div>

        <div class="content flex-grow-1">
            <div class="container-fluid">
                <!-- Header -->
                <div class="content-header">
                    <h2><i class="fa-solid fa-film"></i>Content Management</h2>
                </div>

                <!-- Search Section -->
                <div class="search-section">
                    <h5><i class="fa-brands fa-youtube"></i>Cari Video di YouTube</h5>
                    <div class="search-input-group">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="searchQuery" class="form-control" placeholder="Masukkan kata kunci pencarian...">
                        <button id="searchBtn">
                            Search
                        </button>
                    </div>
                </div>

                <!-- Search Results -->
                <div id="searchResultsContainer" style="display: none;">
                    <div class="results-section">
                        <div id="searchResults" class="row g-3"></div>
                    </div>
                </div>

                <!-- Saved Videos -->
                <div class="saved-section">
                    <h4><i class="fa-solid fa-bookmark"></i>Video Tersimpan</h4>
                    
                    <?php if (!empty($videos)): ?>
                        <div class="row g-3">
                            <?php foreach ($videos as $v): ?>
                                <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
                                    <div class="card video-card">
                                        <img src="<?= htmlspecialchars($v['thumbnail_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($v['title']) ?>">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($v['title']) ?></h6>
                                            <span class="playlist-badge"><?= htmlspecialchars($v['playlist_name']) ?></span>
                                            <a href="../../controllers/video_action.php?action=delete&id=<?= $v['id'] ?>" 
                                               class="btn btn-delete w-100" 
                                               onclick="return confirm('Hapus video ini?')">
                                                <i class="fa-solid fa-trash"></i>
                                                Hapus
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-video-slash"></i>
                            <p>Belum ada video tersimpan</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Ambil API key dari PHP (sudah di-load dari .env)
    const YT_API_KEY = "<?= htmlspecialchars($youtubeApiKey) ?>";
    const playlists = <?= json_encode($playlists) ?>;
    const searchResultsContainer = document.getElementById('searchResultsContainer');
    const searchResults = document.getElementById('searchResults');

    document.getElementById('searchBtn').addEventListener('click', searchVideos);
    document.getElementById('searchQuery').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') searchVideos();
    });

    async function searchVideos() {
        const query = document.getElementById('searchQuery').value.trim();
        if (!query) {
            alert("Masukkan kata kunci!");
            return;
        }

            searchResults.innerHTML = '<div class="loading"><i class="fa-solid fa-spinner"></i><p class="mt-2">Mencari video...</p></div>';
            searchResultsContainer.style.display = 'block';

            const url = `https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&q=${encodeURIComponent(query)}&maxResults=12&key=${YT_API_KEY}`;

            try {
                const res = await fetch(url);
                const data = await res.json();
                const items = data.items || [];
                
                searchResults.innerHTML = '';

                if (items.length === 0) {
                    searchResults.innerHTML = '<div class="empty-state"><i class="fa-solid fa-magnifying-glass"></i><p>Tidak ada hasil ditemukan</p></div>';
                    return;
                }

                items.forEach(v => {
                    const videoCard = document.createElement('div');
                    videoCard.className = 'col-xl-3 col-lg-4 col-md-6';
                    videoCard.innerHTML = `
                        <div class="card video-card">
                            <img src="${v.snippet.thumbnails.medium.url}" class="card-img-top" alt="${v.snippet.title}">
                            <div class="card-body">
                                <h6 class="card-title">${v.snippet.title}</h6>
                                <p class="card-text">${v.snippet.description || 'Tidak ada deskripsi'}</p>
                                <form action="../../controllers/video_action.php?action=store" method="POST">
                                    <input type="hidden" name="youtube_video_id" value="${v.id.videoId}">
                                    <input type="hidden" name="title" value="${v.snippet.title}">
                                    <input type="hidden" name="description" value="${v.snippet.description || ''}">
                                    <input type="hidden" name="thumbnail" value="${v.snippet.thumbnails.medium.url}">
                                    <select name="playlist_id" class="form-select" required>
                                        <option value="">Pilih Playlist</option>
                                        ${playlists.map(p => `<option value="${p.id}">${p.name}</option>`).join('')}
                                    </select>
                                    <button type="submit" class="btn btn-add w-100">
                                        <i class="fa-solid fa-plus"></i>
                                        Tambah ke Database
                                    </button>
                                </form>
                            </div>
                        </div>
                    `;
                    searchResults.appendChild(videoCard);
                });
            } catch (err) {
                console.error(err);
                searchResults.innerHTML = '<div class="empty-state"><i class="fa-solid fa-triangle-exclamation"></i><p>Gagal mengambil data dari YouTube API</p></div>';
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
