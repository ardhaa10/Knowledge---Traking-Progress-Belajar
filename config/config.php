<?php
// Deteksi environment berdasarkan HTTP_HOST
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocalhost = in_array($host, ['localhost', '127.0.0.1', '::1']);

if ($isLocalhost) {
    // Localhost
    define('BASE_URL', '/belajar-online');
    define('FULL_URL', 'http://localhost/belajar-online');
} else {
    // Production (knowledgeapp.my.id)
    define('BASE_URL', '');
    define('FULL_URL', 'https://' . $host);
}

// Helper function untuk generate URL
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}