<?php
session_start();
require_once __DIR__ . '/../../../koneksi.php';

if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

/* =========================
   FILTER INPUT (AMAN)
========================= */
$minProgress = isset($_GET['min_progress'])
    ? max(0, min(100, (int)$_GET['min_progress']))
    : 0;

$lastActive = $_GET['last_active'] ?? '';

/* =========================
   QUERY FIX - ORDER BY SQL LANGSUNG
========================= */
$query = "
SELECT 
    u.name,
    COUNT(vp.id) AS total_videos,
    COALESCE(ROUND(AVG(vp.progress),0), 0) AS avg_progress,
    SUM(CASE WHEN vp.progress = 100 THEN 1 ELSE 0 END) AS completed_videos,
    MAX(vp.updated_at) AS last_active
FROM users u
LEFT JOIN video_progress vp ON vp.user_id = u.id
WHERE u.role = 'user'";

$params = [];
$types = "";

// Filter tanggal DI WHERE (sebelum GROUP BY)
if (!empty($lastActive)) {
    $query .= " AND vp.updated_at >= ?";
    $params[] = $lastActive . ' 00:00:00';
    $types .= "s";
}

$query .= "
GROUP BY u.id
HAVING AVG(vp.progress) >= ?
ORDER BY 
    SUM(CASE WHEN vp.progress = 100 THEN 1 ELSE 0 END) DESC,  -- 1. SELSAI TERBANYAK
    (SUM(CASE WHEN vp.progress = 100 THEN 1 ELSE 0 END) * AVG(vp.progress)) DESC,  -- 2. KUALITAS
    COUNT(vp.id) DESC";

$params[] = $minProgress;
$types .= "i";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$reports = $result->fetch_all(MYSQLI_ASSOC);

/* =========================
   USER TERBAIK (SUDAH TERURUT)
========================= */
$topUser = $reports[0] ?? null;

/* =========================
   STATISTIK
========================= */
$totalUsers = count($reports);
$avgAll = $totalUsers 
    ? round(array_sum(array_column($reports, 'avg_progress')) / $totalUsers)
    : 0;

$activeUsers = count(array_filter($reports, fn($r) => !empty($r['last_active'])));

// Tampilkan hasil (contoh HTML)
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports - Admin Panel</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- === STYLE TIDAK DIUBAH === -->
<style>
body {
  font-family: "Inter", sans-serif;
  background: #f8fafc;
  margin: 0;
}

/* ===== LAYOUT ===== */
.layout {
  display: flex;
  min-height: 100vh;
}

/* ===== SIDEBAR ===== */
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  color: #fff;
  padding: 2rem 1.5rem;
  box-shadow: 4px 0 20px rgba(0,0,0,.1);
}

.sidebar h4 {
  font-weight: 700;
  font-size: 1.4rem;
  margin-bottom: 2rem;
  background: linear-gradient(135deg,#667eea,#764ba2);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.sidebar .nav-link {
  color: rgba(255,255,255,.7);
  padding: .85rem 1.25rem;
  margin-bottom: .4rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: .75rem;
  font-weight: 500;
  transition: all .3s ease;
}

.sidebar .nav-link i {
  width: 20px;
  text-align: center;
}

.sidebar .nav-link:hover {
  background: rgba(255,255,255,.1);
  color: #fff;
  transform: translateX(4px);
}

.sidebar .nav-link.active {
  background: linear-gradient(135deg,#667eea,#764ba2);
  color: #fff;
  box-shadow: 0 4px 12px rgba(102,126,234,.4);
}

/* ===== MAIN ===== */
.main-content {
  flex-grow: 1;
  padding: 2.5rem 3rem;
}

/* ===== HEADER ===== */
.page-header {
  margin-bottom: 2rem;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 1.5rem;
}

.page-header h2 {
  font-weight: 700;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: .75rem;
}

.page-header p {
  color: #64748b;
}

/* ===== STAT CARD ===== */
.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #fff;
  padding: 1.75rem;
  border-radius: 18px;
  box-shadow: 0 6px 18px rgba(0,0,0,.08);
}

.stat-label {
  font-size: .75rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: .05em;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #667eea;
}

/* ===== TABLE ===== */
.table-card {
  background: #fff;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 6px 18px rgba(0,0,0,.08);
}

.table thead th {
  background: #f1f5f9;
  font-size: .75rem;
  text-transform: uppercase;
  color: #475569;
  border: none;
}

.table tbody td {
  vertical-align: middle;
  border-color: #e2e8f0;
}

/* ===== BADGE ===== */
.badge-progress {
  padding: .35rem .8rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: .85rem;
}

.good {
  background: #10b981;
  color: #fff;
}

.bad {
  background: #ef4444;
  color: #fff;
}

/* ===== TOP USER ===== */
.top-user {
  background: linear-gradient(135deg,#667eea,#764ba2);
  color: #fff;
  border-radius: 18px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 10px 30px rgba(102,126,234,.35);
}

</style>
</head>

<body>
<div class="layout">

<!-- SIDEBAR -->
<div class="sidebar flex-shrink-0" style="width:280px">
  <h4><i class="fa-solid fa-shield-halved me-2"></i>Admin Panel</h4>
  <ul class="nav nav-pills flex-column mb-auto">
    <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i>Dashboard</a></li>
    <li><a href="user_management.php" class="nav-link"><i class="fa-solid fa-users"></i>Manage Users</a></li>
    <li><a href="playlist_management.php" class="nav-link"><i class="fa-solid fa-layer-group"></i>Manage Playlist</a></li>
    <li><a href="content_management.php" class="nav-link"><i class="fa-solid fa-film"></i>Manage Content</a></li>
    <li><a href="jadwal_management.php" class="nav-link"><i class="fa-solid fa-calendar"></i>Manage Jadwal</a></li>
    <li><a href="reports.php" class="nav-link active"><i class="fa-solid fa-chart-bar"></i>Reports</a></li>
    <li><a href="../../controllers/auth_action.php?action=logout" class="nav-link"><i class="fa-solid fa-right-from-bracket"></i>Logout</a></li>
  </ul>
</div>

<!-- CONTENT -->
<div class="main-content">

<div class="page-header">
  <h2><i class="fa-solid fa-chart-bar"></i> Reports</h2>
  <p>Analitik & performa pembelajaran user</p>
</div>

<!-- USER TERBAIK -->
<?php if ($topUser): ?>
<div class="top-user">
  <h5><i class="fa-solid fa-crown"></i> User Terbaik</h5>
  <p class="mb-1"><?= htmlspecialchars($topUser['name']) ?></p>
  <strong><?= $topUser['avg_progress'] ?>% Progress</strong>
</div>
<?php endif; ?>

<!-- STAT -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-label">Total User</div>
    <div class="stat-value"><?= $totalUsers ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Rata-rata Progress</div>
    <div class="stat-value"><?= $avgAll ?>%</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">User Aktif</div>
    <div class="stat-value"><?= $activeUsers ?></div>
  </div>
</div>

<!-- FILTER + CSV -->
<div class="table-card mb-4">
<form method="get" class="row g-3">
  <div class="col-md-4">
    <label class="form-label">Min Progress (%)</label>
    <input
      type="number"
      name="min_progress"
      class="form-control"
      min="0"
      max="100"
      step="1"
      value="<?= htmlspecialchars($minProgress) ?>"
    >
  </div>

  <div class="col-md-4">
    <label class="form-label">Aktif Setelah</label>
    <input
      type="date"
      name="last_active"
      class="form-control"
      value="<?= htmlspecialchars($lastActive) ?>"
    >
  </div>

  <div class="col-md-4 d-flex align-items-end gap-2">
    <button class="btn btn-primary w-100">
      <i class="fa-solid fa-filter"></i> Terapkan
    </button>

    <a
      href="../../controllers/export_reports.php?<?= http_build_query($_GET) ?>"
      class="btn btn-success w-100"
    >
      <i class="fa-solid fa-file-csv"></i> CSV
    </a>
  </div>
</form>
</div>

<!-- TABLE -->
<div class="table-card">
<table class="table">
<thead>
<tr>
  <th>Name</th>
  <th>Avg Progress</th>
  <th>Video Selesai</th>
  <th>Last Active</th>
</tr>
</thead>
<tbody>
<?php foreach ($reports as $r): ?>
<tr>
  <td><?= htmlspecialchars($r['name']) ?></td>
  <td>
    <span class="badge-progress <?= $r['avg_progress'] >= 50 ? 'good':'bad' ?>">
      <?= $r['avg_progress'] ?>%
    </span>
  </td>
  <td><?= $r['completed_videos'] ?></td>
  <td><?= $r['last_active'] ?? '-' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

</div>
</div>
</body>
</html>
