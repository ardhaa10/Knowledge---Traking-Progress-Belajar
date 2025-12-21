<?php
require_once 'Database.php';

class Dashboard
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getDashboardData($user_id)
    {
        // Total playlists (courses)
        $totalPlaylists = $this->conn->query("
        SELECT COUNT(*) AS total FROM playlists
    ")->fetch_assoc()['total'];

        // Total videos
        $totalVideos = $this->conn->query("
        SELECT COUNT(*) AS total FROM videos
    ")->fetch_assoc()['total'];

        // Progress per playlist
        $playlistProgressQuery = $this->conn->query("
        SELECT p.id, p.name,
            ROUND(AVG(vp.progress),0) AS progress
        FROM playlists p
        LEFT JOIN videos v ON v.playlist_id = p.id
        LEFT JOIN video_progress vp ON vp.video_id = v.id AND vp.user_id = $user_id
        GROUP BY p.id
    ");

        $playlistProgress = $playlistProgressQuery->fetch_all(MYSQLI_ASSOC);

        // Global average progress
        $avgProgress = $this->conn->query("
        SELECT ROUND(AVG(progress),0) AS avg_progress 
        FROM video_progress 
        WHERE user_id = $user_id
    ")->fetch_assoc()['avg_progress'] ?? 0;

        // Completed videos
        $completedVideos = $this->conn->query("
        SELECT COUNT(*) AS total FROM video_progress 
        WHERE user_id = $user_id AND progress = 100
    ")->fetch_assoc()['total'];

        // Favorite learning hour
        $favoriteTime = $this->conn->query("
        SELECT HOUR(updated_at) AS hour, COUNT(*) AS hits
        FROM video_progress
        WHERE user_id = $user_id AND updated_at IS NOT NULL
        GROUP BY HOUR(updated_at)
        ORDER BY hits DESC
        LIMIT 1
    ")->fetch_assoc();

        // === Total Streak ===
        $streak = $this->conn->query("
        SELECT streak_count FROM streaks WHERE user_id = $user_id
    ")->fetch_assoc()['streak_count'] ?? 0;

        return [
            "totalPlaylists" => $totalPlaylists,
            "totalVideos" => $totalVideos,
            "playlistProgress" => $playlistProgress,
            "avgProgress" => $avgProgress,
            "completedVideos" => $completedVideos,
            "favoriteLearningHour" => $favoriteTime['hour'] ?? null,
            "totalStreak" => $streak
        ];
    }

    public function getAdminDashboardData()
    {
        // Total user
        $totalUsers = $this->conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'user'")->fetch_assoc()['total'];

        // Total playlists
        $totalPlaylists = $this->conn->query("SELECT COUNT(*) AS total FROM playlists")->fetch_assoc()['total'];

        // Total videos
        $totalVideos = $this->conn->query("SELECT COUNT(*) AS total FROM videos")->fetch_assoc()['total'];

        // Total video progress 100% (selesai) untuk semua user
        $completedVideos = $this->conn->query("SELECT COUNT(*) AS total FROM video_progress WHERE progress = 100")->fetch_assoc()['total'];

        // Rata-rata streak semua user
        $avgStreak = $this->conn->query("SELECT ROUND(AVG(streak_count),0) AS avg_streak FROM streaks")->fetch_assoc()['avg_streak'] ?? 0;

        // Playlist paling populer (paling banyak diakses)
        $popularPlaylist = $this->conn->query("
        SELECT p.name, COUNT(vp.id) AS hits
        FROM playlists p
        LEFT JOIN videos v ON v.playlist_id = p.id
        LEFT JOIN video_progress vp ON vp.video_id = v.id
        GROUP BY p.id
        ORDER BY hits DESC
        LIMIT 1
    ")->fetch_assoc();

        return [
            "totalUsers" => $totalUsers,
            "totalPlaylists" => $totalPlaylists,
            "totalVideos" => $totalVideos,
            "completedVideos" => $completedVideos,
            "avgStreak" => $avgStreak,
            "popularPlaylist" => $popularPlaylist['name'] ?? 'Belum ada'
        ];
    }
}
