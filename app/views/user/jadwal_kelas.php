<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../models/Jadwal.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$jadwalModel = new Jadwal();
$jadwalList = $jadwalModel->getAll();

$today = date("Y-m-d");
$todaySchedules = array_filter($jadwalList, fn($j) => $j['tanggal'] === $today);

// Format event untuk FullCalendar
$events = [];
foreach ($jadwalList as $j) {
    $events[] = [
        'id' => $j['id'],
        'title' => $j['mata_kuliah'],
        'start' => $j['tanggal'] . "T" . $j['jam_mulai'],
        'end'   => $j['tanggal'] . "T" . $j['jam_selesai'],
        'extendedProps' => [
            'deskripsi' => $j['deskripsi']
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kelas</title>

    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

    <!-- Bootstrap + FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
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

        /* Container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Header Section */
        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-header h3 {
            color: #fff;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1rem;
        }

        /* Alert Cards */
        .alert {
            border: none;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .alert-warning {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #fff;
        }

        .alert-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
        }

        .alert strong {
            font-weight: 600;
        }

        .alert ul {
            padding-left: 1.5rem;
            margin-top: 0.75rem;
            margin-bottom: 0;
        }

        .alert li {
            margin-bottom: 0.5rem;
        }

        .alert li:last-child {
            margin-bottom: 0;
        }

        /* Calendar Card */
        .calendar-wrapper {
            background: #fff;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        #calendar {
            background: transparent;
            padding: 0;
            border-radius: 0;
            box-shadow: none;
        }

        /* ============================
   Modern Clean FullCalendar UI
   ============================ */

        .fc {
            font-family: "Inter", sans-serif;
            --fc-border-color: #e2e8f0;
        }

        /* Toolbar */
        .fc-toolbar-title {
            font-size: 1.6rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
        }

        .fc-button {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            color: #334155 !important;
            border-radius: 8px !important;
            padding: 0.45rem 0.9rem !important;
            font-weight: 600 !important;
            transition: all .25s ease !important;
        }

        .fc-button:hover {
            background: #f1f5f9 !important;
            transform: translateY(-2px) !important;
        }

        .fc-button-active {
            background: #0f172a !important;
            color: white !important;
        }

        /* Header Days */
        .fc-col-header-cell {
            background: #f8fafc !important;
            padding: 0.8rem 0 !important;
            font-weight: 700 !important;
            color: #334155 !important;
            border: none !important;
        }

        /* Day Cell */
        .fc-daygrid-day {
            transition: background .2s ease;
        }

        .fc-daygrid-day:hover {
            background: #f8fafc !important;
        }

        /* Today Highlight */
        .fc-day-today {
            background: rgba(99, 102, 241, 0.07) !important;
            backdrop-filter: blur(4px);
        }

        /* Day Numbers */
        .fc-daygrid-day-number {
            padding: 0.5rem !important;
            font-size: 0.95rem;
            font-weight: 600;
            color: #475569;
        }

        /* Today Number Style */
        .fc-day-today .fc-daygrid-day-number {
            background: #6366f1;
            padding: 6px 10px !important;
            border-radius: 8px;
            color: white !important;
            font-weight: 700;
        }

        /* Event Style Modern Glass */
        .fc-event {
            border: none !important;
            border-radius: 10px !important;
            padding: 6px 10px !important;
            font-weight: 600 !important;
            background: rgba(99, 102, 241, 0.9) !important;
            backdrop-filter: blur(6px);
            color: white !important;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.25);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        /* Hover Event */
        .fc-event:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.45);
        }

        /* Event Title and clickable fix */
        .fc-event-title,
        .fc-daygrid-event>.fc-event-title {
            font-size: 0.9rem !important;
            color: white !important;
            text-decoration: none !important;
        }

        /* Remove calendar event underline + blue link */
        a.fc-event,
        .fc-daygrid-dot-event {
            color: white !important;
            text-decoration: none !important;
        }

        /* Hilangkan warna biru dan underline dari teks event */
        .fc-daygrid-event>.fc-event-main {
            color: black !important;
            text-decoration: none !important;
        }

        /* Hilangkan underline pada wrapper hyperlink */
        .fc-daygrid-event {
            text-decoration: none !important;
        }

        /* Ubah warna dot (default biru) */
        .fc-daygrid-event-dot {
            border-color: white !important;
        }


        /* Custom Modal Styling */
        .swal2-popup {
            border-radius: 20px !important;
            padding: 2rem !important;
        }

        .swal2-title {
            color: #1e293b !important;
            font-weight: 700 !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header h3 {
                font-size: 1.5rem;
            }

            .calendar-wrapper {
                padding: 1rem;
            }

            .fc-toolbar {
                flex-direction: column;
                gap: 1rem;
            }

            .fc-toolbar-title {
                font-size: 1.25rem !important;
            }

            .alert {
                padding: 1rem;
            }
        }

        /* Custom scrollbar */
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
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Knowledge</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a href="<?= url('/dashboard') ?>" class="nav-link">Dashboard</a></li>
                <li class="nav-item"><a href="<?= url('/jadwal') ?>" class="nav-link active">Jadwal</a></li>
                <li class="nav-item"><a href="<?= url('/kelas') ?>" class="nav-link">Kelas</a></li>
                <li class="nav-item"><a href="<?= url('/profile') ?>" class="nav-link">Profile</a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>/app/controllers/auth_action.php?action=logout" class="nav-link">Logout</a>
            </ul>
            </div>
        </div>
    </nav>

    <!-- CONTENT -->
    <div class="main-container">

        <div class="page-header">
            <h3>
                <i class="fa-solid fa-calendar-days"></i>
                Jadwal Kelas
            </h3>
            <p>Lihat jadwal kuliah lengkap di kalender</p>
        </div>

        <?php if (count($todaySchedules) > 0): ?>
            <div class="alert alert-warning">
                <i class="fa-solid fa-bell fa-shake me-2"></i>
                <strong>Jadwal Hari Ini:</strong>
                <ul>
                    <?php foreach ($todaySchedules as $t): ?>
                        <li>
                            <b><?= htmlspecialchars($t['mata_kuliah']) ?></b> —
                            <?= $t['jam_mulai'] ?> - <?= $t['jam_selesai'] ?>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle me-2"></i>
                Tidak ada jadwal kelas hari ini 🎉
            </div>
        <?php endif; ?>

        <div class="calendar-wrapper">
            <div id="calendar"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var calendar = new FullCalendar.Calendar(document.getElementById("calendar"), {
                initialView: "dayGridMonth",
                selectable: true,
                headerToolbar: {
                    left: "prev,next today",
                    center: "title",
                    right: "dayGridMonth,timeGridWeek,timeGridDay"
                },
                events: <?= json_encode($events) ?>,

                // Hanya tampilkan title
                eventContent: function(arg) {
                    let titleEl = document.createElement('div');
                    titleEl.innerHTML = arg.event.title;
                    return {
                        domNodes: [titleEl]
                    };
                },

                eventClick: function(info) {
                    const startTime = info.event.start.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    const endTime = info.event.end.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    alert(
                        "📘 Mata Kuliah: " + info.event.title +
                        "\n🕒 Pukul: " + startTime + " - " + endTime +
                        "\n📝 Deskripsi: " + info.event.extendedProps.deskripsi
                    );
                }
            });

            calendar.render();
        });
    </script>

</body>

</html>