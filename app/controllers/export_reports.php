<?php
session_start();
require_once __DIR__ . '/../../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Akses ditolak');
}

/* =========================
   FILTER INPUT (AMAN)
========================= */
$minProgress = isset($_GET['min_progress'])
    ? max(0, min(100, (int)$_GET['min_progress']))
    : 0;

$lastActive = $_GET['last_active'] ?? '';

/* =========================
   QUERY LENGKAP - PRIORITAS: SELSAI TERBANYAK DULU
========================= */
$sql = "
SELECT 
    u.name,
    COUNT(vp.id) AS total_video,
    SUM(CASE WHEN vp.progress = 100 THEN 1 ELSE 0 END) AS completed,
    ROUND(AVG(vp.progress),2) AS avg_progress,
    MAX(vp.updated_at) AS last_activity
FROM users u
LEFT JOIN video_progress vp ON vp.user_id = u.id
WHERE u.role = 'user'";

$params = [];
$types = "";

// Filter tanggal (opsional)
if (!empty($lastActive)) {
    $sql .= " AND vp.updated_at >= ?";
    $params[] = $lastActive . ' 00:00:00';
    $types .= "s";
}

$sql .= "
GROUP BY u.id
HAVING avg_progress >= ?
ORDER BY 
    -- PRIORITAS 1: JUMLAH VIDEO SELSAI (PALING PENTING)
    SUM(CASE WHEN vp.progress = 100 THEN 1 ELSE 0 END) DESC,
    -- PRIORITAS 2: KUALITAS x KUANTITAS (completed * avg)
    (SUM(CASE WHEN vp.progress = 100 THEN 1 ELSE 0 END) * AVG(vp.progress)) DESC,
    -- PRIORITAS 3: TOTAL VIDEO (volume)
    COUNT(vp.id) DESC,
    -- PRIORITAS 4: AKTIVITAS TERBARU
    MAX(vp.updated_at) DESC";

$params[] = $minProgress;
$types .= "i";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

/* =========================
   CSV OUTPUT
========================= */
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=laporan_progress.csv');

$output = fopen('php://output', 'w');

// Header CSV
fputcsv($output, [
    'Nama', 
    'Total Video', 
    'Video Selesai', 
    'Rata-rata Progress (%)', 
    'Last Activity'
]);

// Data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['name'],
        $row['total_video'],
        $row['completed'],
        $row['avg_progress'],
        $row['last_activity']
    ]);
}

fclose($output);
exit;
?>
