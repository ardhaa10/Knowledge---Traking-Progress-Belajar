<?php
// ==================================================
// LOAD .env LANGSUNG (FIX PATH & WINDOWS FRIENDLY)
// File ini berada di: app/views/auth/login-proses.php
// .env berada di: root project (belajar-online/.env)
// ==================================================
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
    putenv("$key=$value"); // penting untuk Windows
}
// ==================================================

require_once __DIR__ . '/../../controllers/AuthController.php';

$controller = new AuthController();

if (isset($_GET['code'])) {

    $code = $_GET['code'];

    // AMBIL DARI .env
    $client_id     = getenv('GOOGLE_CLIENT_ID');
    $client_secret = getenv('GOOGLE_CLIENT_SECRET');
    $redirect_uri  = getenv('GOOGLE_REDIRECT_URI');

    // VALIDASI ENV
    if (!$client_id || !$client_secret || !$redirect_uri) {
        die('Google OAuth ENV belum lengkap');
    }

    // ================= REQUEST TOKEN =================
    $token_url = "https://oauth2.googleapis.com/token";

    $post_fields = [
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$response) {
        die("Google tidak merespon sama sekali.");
    }

    $token_data = json_decode($response, true);

    if (isset($token_data['error'])) {
        die("<h3>Google OAuth Error</h3>
            <b>Error:</b> {$token_data['error']}<br>
            <b>Description:</b> " . ($token_data['error_description'] ?? '-'));
    }

    if (!isset($token_data['access_token'])) {
        die("<h3>Access token kosong</h3><pre>$response</pre>");
    }

    $access_token  = $token_data['access_token'];
    $refresh_token = $token_data['refresh_token'] ?? null;
    $expires_in    = $token_data['expires_in'] ?? null;

    // ================= USER INFO =================
    $ch = curl_init("https://www.googleapis.com/oauth2/v3/userinfo?access_token=" . $access_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $user_info_json = curl_exec($ch);
    curl_close($ch);

    $user_info = json_decode($user_info_json, true);

    if (!isset($user_info['sub'])) {
        die("Data user tidak lengkap dari Google");
    }

    // ================= LOGIN USER =================
    $controller->loginGoogle(
        $user_info['sub'],
        $user_info['name'] ?? '',
        $user_info['email'] ?? '',
        $access_token,
        $refresh_token,
        $expires_in,
        $user_info['picture'] ?? null
    );

    

} else {
    header("Location: ../../views/auth/login.php");
    exit;
}
