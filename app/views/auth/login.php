<?php
require_once __DIR__ . '/../../../config/config.php';
// ================= LOAD .env LANGSUNG =================
// File ini: app/views/auth/login.php
// .env di: root project (belajar-online/.env)
$envFile = __DIR__ . '/../../../.env';

if (!file_exists($envFile)) {
    die('.env file not found');
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;

    [$key, $value] = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value);

    $_ENV[$key] = $value;
    putenv("$key=$value");
}
// ======================================================

require_once __DIR__ . '/../../controllers/AuthController.php';

// Ambil dari ENV
$client_id    = getenv('GOOGLE_CLIENT_ID');
$redirect_uri = getenv('GOOGLE_REDIRECT_URI');

// Validasi ENV
if (!$client_id || !$redirect_uri) {
    die('Google OAuth ENV belum lengkap');
}

// Scope Google
$scope = urlencode(
    "https://www.googleapis.com/auth/userinfo.email " .
    "https://www.googleapis.com/auth/userinfo.profile " .
    "https://www.googleapis.com/auth/calendar"
);

// Google OAuth URL
$auth_url = "https://accounts.google.com/o/oauth2/auth?" .
    "response_type=code&" .
    "client_id={$client_id}&" .
    "redirect_uri={$redirect_uri}&" .
    "scope={$scope}&" .
    "access_type=offline&" .
    "prompt=consent";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Belajar Online</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #667eea, #764ba2);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .card-login {
        max-width: 400px;
        width: 100%;
        background-color: #fff;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card-login:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    }

    h3 {
        font-weight: 600;
        margin-bottom: 1.5rem;
        text-align: center;
        color: #1e293b;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .btn-primary {
        background: #667eea;
        border: none;
    }

    .btn-primary:hover {
        background: #556cd6;
    }

    .btn-google {
        border: 1px solid #dd4b39;
        color: #dd4b39;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-google:hover {
        background: #dd4b39;
        color: #fff;
    }

    .text-center a {
        color: #667eea;
        font-weight: 500;
        text-decoration: none;
    }

    .text-center a:hover {
        text-decoration: underline;
    }

    .alert {
        font-size: 0.875rem;
    }
</style>
</head>
<body>

<div class="card-login">
    <h3>Login</h3>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <!-- LOGIN MANUAL -->
     <form action="/belajar-online/app/controllers/auth_action.php?action=login" method="POST">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required placeholder="email@example.com">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="••••••••">
        </div>
        <button class="btn btn-primary w-100 mb-3">Login</button>
    </form>

    <!-- LOGIN GOOGLE -->
    <a href="<?= htmlspecialchars($auth_url) ?>" class="btn btn-google w-100 mb-3">
        <img src="https://img.icons8.com/color/48/000000/google-logo.png" alt="Google Logo" style="width:20px;">
        Login dengan Google
    </a>

    <p class="text-center">
        Belum punya akun? <a href="register.php">Daftar sekarang</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
