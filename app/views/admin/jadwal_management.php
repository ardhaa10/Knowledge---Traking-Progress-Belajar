<?php
session_start();
require_once __DIR__ . '/../../controllers/JadwalController.php';
require_once __DIR__ . '/../../models/Playlist.php';

if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../views/auth/login.php");
    exit;
}

$playlistModel = new Playlist();
$playlists = $playlistModel->getAllPlaylists();

$controller = new JadwalController();
$jadwals = $controller->index();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jadwal - Admin</title>
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
            min-height: 100vh;
        }

        /* Sidebar */
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

        /* Main Content */
        .main-content {
            padding: 2.5rem 3rem;
        }

        .page-header {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .page-header h2 {
            font-weight: 700;
            font-size: 2rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .page-header h2 i {
            color: #667eea;
        }

        .page-header p {
            color: #64748b;
            margin: 0;
        }

        /* Alert */
        .alert {
            border: none;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .alert-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
        }

        .alert i {
            font-size: 1.5rem;
        }

        /* Form Card */
        .form-card {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 2.5rem;
        }

        .form-card h5 {
            font-weight: 700;
            font-size: 1.25rem;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-card h5 i {
            color: #667eea;
        }

        .form-control,
        .form-select {
            height: 48px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.9375rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-label {
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 0.875rem 2rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
            color: #fff;
        }

        /* Table Section */
        .table-card {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-header h5 {
            font-weight: 700;
            font-size: 1.25rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .table-header h5 i {
            color: #667eea;
        }

        .count-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: #475569;
            font-weight: 600;
            border: none;
            padding: 1rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .date-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            display: inline-block;
        }

        .time-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8125rem;
            display: inline-block;
        }

        .course-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9375rem;
        }

        .btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            color: #fff;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            color: #64748b;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #94a3b8;
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 1.5rem 1rem;
            }

            .page-header h2 {
                font-size: 1.5rem;
            }

            .form-card,
            .table-card {
                padding: 1.5rem;
            }

            .table-responsive {
                border-radius: 12px;
                overflow-x: auto;
            }

            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
                font-size: 0.8125rem;
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
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
                <li><a href="content_management.php" class="nav-link"><i class="fa-solid fa-film"></i>Manage Content</a></li>
                <li><a href="jadwal_management.php" class="nav-link active"><i class="fa-solid fa-calendar"></i>Manage Jadwal</a></li>
                <li><a href="reports.php" class="nav-link"><i class="fa-solid fa-chart-bar"></i>Reports</a></li>
                <li><a href="../../controllers/auth_action.php?action=logout" class="nav-link"><i class="fa-solid fa-right-from-bracket"></i>Logout</a></li>
            </ul>
        </div>

        <div class="main-content flex-grow-1">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <h2>
                        <i class="fa-solid fa-calendar-days"></i>
                        Manage Jadwal
                    </h2>
                    <p>Kelola jadwal kelas dan mata kuliah</p>
                </div>

                <!-- Success Alert -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?= htmlspecialchars($_GET['success']) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Form Card -->
                <div class="form-card">
                    <h5>
                        <i class="fa-solid fa-plus-circle"></i>
                        Tambah Jadwal Baru
                    </h5>
                    <form action="../../controllers/jadwal_action.php?action=store" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fa-solid fa-book me-1"></i>
                                    Mata Kuliah / Playlist
                                </label>
                                <select name="mata_kuliah" class="form-select" required>
                                    <option value="">-- Pilih Mata Kuliah --</option>
                                    <?php foreach ($playlists as $p): ?>
                                        <option value="<?= htmlspecialchars($p['name']) ?>">
                                            <?= htmlspecialchars($p['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fa-solid fa-calendar me-1"></i>
                                    Tanggal
                                </label>
                                <input type="date" name="tanggal" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fa-solid fa-clock me-1"></i>
                                    Jam Mulai
                                </label>
                                <input type="time" name="jam_mulai" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fa-solid fa-clock me-1"></i>
                                    Jam Selesai
                                </label>
                                <input type="time" name="jam_selesai" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fa-solid fa-file-lines me-1"></i>
                                    Deskripsi
                                </label>
                                <input type="text" name="deskripsi" class="form-control" placeholder="Topik atau catatan...">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn-submit">
                                <i class="fa-solid fa-plus"></i>
                                Tambah Jadwal
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table Card -->
                <div class="table-card">
                    <div class="table-header">
                        <h5>
                            <i class="fa-solid fa-list-check"></i>
                            Daftar Jadwal
                        </h5>
                        <span class="count-badge"><?= count($jadwals) ?> jadwal</span>
                    </div>

                    <?php if (empty($jadwals)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-calendar-xmark"></i>
                            <h5>Belum Ada Jadwal</h5>
                            <p>Tambahkan jadwal baru menggunakan form di atas</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><i class="fa-solid fa-book me-2"></i>Mata Kuliah</th>
                                        <th><i class="fa-solid fa-calendar me-2"></i>Tanggal</th>
                                        <th><i class="fa-solid fa-clock me-2"></i>Waktu</th>
                                        <th><i class="fa-solid fa-file-lines me-2"></i>Deskripsi</th>
                                        <th><i class="fa-solid fa-gear me-2"></i>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jadwals as $j): ?>
                                        <tr>
                                            <td>
                                                <span class="course-name"><?= htmlspecialchars($j['mata_kuliah']) ?></span>
                                            </td>
                                            <td>
                                                <span class="date-badge">
                                                    <?= date('d M Y', strtotime($j['tanggal'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="time-badge">
                                                    <?= htmlspecialchars($j['jam_mulai']) ?> - <?= htmlspecialchars($j['jam_selesai']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($j['deskripsi']) ?></td>
                                            <td>
                                                <a href="../../controllers/jadwal_action.php?action=delete&id=<?= $j['id'] ?>"
                                                    class="btn-delete"
                                                    onclick="return confirm('Hapus jadwal ini?')">
                                                    <i class="fa-solid fa-trash"></i>
                                                    Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>