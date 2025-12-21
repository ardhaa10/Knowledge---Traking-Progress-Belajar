<?php

$routes = [
    // Home
    '/' => 'app/views/home.php',
    
    // Auth Routes
    '/login' => 'app/views/auth/login.php',
    '/register' => 'app/views/auth/register.php',
    
    // Admin Routes (Protected)
    '/admin' => function() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/app/views/admin/dashboard.php';
    },
    
    '/admin/content' => function() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/app/views/admin/content_management.php';
    },
    
    '/admin/users' => function() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/app/views/admin/user_management.php';
    },
    
    '/admin/playlists' => function() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/app/views/admin/playlist_management.php';
    },
    
    '/admin/jadwal' => function() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/app/views/admin/jadwal_management.php';
    },
    
    '/admin/reports' => function() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/app/views/admin/reports.php';
    },
    
    // User Routes (Protected)
    '/dashboard' => function() {
        if (!isset($_SESSION['user_name'])) {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/app/views/user/dashboard.php';
    },
    
    '/playlist/{id}' => function($params) {
        if (!isset($_SESSION['user_name'])) {
            header('Location: /login');
            exit;
        }
        $_GET['id'] = $params['id'];
        require_once __DIR__ . '/app/views/user/playlist.php';
    },
    
    '/video/{id}' => function($params) {
        if (!isset($_SESSION['user_name'])) {
            header('Location: /login');
            exit;
        }
        $_GET['id'] = $params['id'];
        require_once __DIR__ . '/app/views/user/video_player.php';
    },
    
];