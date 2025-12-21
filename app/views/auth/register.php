<?php
// ================= LOAD .env LANGSUNG =================
// File ini: app/views/auth/register.php
// .env di: root project (belajar-online/.env)
$envFile = __DIR__ . '/../../../.env';

if (!file_exists($envFile)) {
    die('.env file not found');
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;

    [$key, $value] = explode('=', $line, 2);
    $_ENV[trim($key)] = trim($value);
    putenv(trim($key) . '=' . trim($value));
}
// ======================================================

require_once __DIR__ . '/../../controllers/AuthController.php';

// Ambil ENV
$client_id    = getenv('GOOGLE_CLIENT_ID');
$redirect_uri = getenv('GOOGLE_REDIRECT_URI');

if (!$client_id || !$redirect_uri) {
    die('Google OAuth ENV belum lengkap');
}

// Scope Google (register cukup basic profile)
$scope = urlencode(
    "https://www.googleapis.com/auth/userinfo.email " .
    "https://www.googleapis.com/auth/userinfo.profile"
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
<title>Register - Knowledge</title>

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

    .card-register {
        max-width: 420px;
        width: 100%;
        background-color: #fff;
        border-radius: 18px;
        padding: 2.5rem 2rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.18);
        transition: all 0.3s ease;
    }

    .card-register:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 35px rgba(0,0,0,0.22);
    }

    h3 {
        font-weight: 600;
        margin-bottom: 0.75rem;
        text-align: center;
        color: #1e293b;
    }

    .subtitle {
        text-align: center;
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 1.75rem;
    }

    .btn-google {
        border: 1px solid #dd4b39;
        color: #dd4b39;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        font-weight: 500;
        padding: 0.75rem;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .btn-google:hover {
        background: #dd4b39;
        color: #fff;
        transform: translateY(-2px);
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 1.5rem 0;
        color: #94a3b8;
        font-size: 0.85rem;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }

    .divider::before {
        margin-right: 0.75rem;
    }

    .divider::after {
        margin-left: 0.75rem;
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

<div class="card-register">
    <h3>Daftar Akun</h3>
    <p class="subtitle">
        Buat akun untuk mulai belajar dan pantau progresmu
    </p>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>


    <form method="POST" action="../../controllers/auth_action.php">
    <input type="hidden" name="action" value="register">

    <div class="mb-3">
        <input type="text"
               name="name"
               class="form-control"
               placeholder="Nama Lengkap"
               required>
    </div>

    <div class="mb-3">
        <input type="email"
               name="email"
               class="form-control"
               placeholder="Email"
               required>
    </div>

    <div class="mb-3">
        <input type="password"
               name="password"
               class="form-control"
               placeholder="Password"
               required>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        Daftar
    </button>
</form>

<div class="divider">atau</div>

    <!-- REGISTER GOOGLE -->
    <a href="<?= htmlspecialchars($auth_url) ?>" class="btn btn-google w-100">
        <img src="https://img.icons8.com/color/48/000000/google-logo.png"
             alt="Google Logo"
             style="width:22px;">
        Register dengan Google
    </a><br></br>

    <p class="text-center">
        Sudah punya akun?
        <a href="login.php">Login di sini</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
