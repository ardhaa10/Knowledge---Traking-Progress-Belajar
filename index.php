<?php
session_start();
require_once __DIR__ . '/routes.php';

// Ambil route dari URL
$route = $_GET['route'] ?? '/';
$route = '/' . trim($route, '/');

// Cari route yang cocok
$matched = false;

foreach ($routes as $pattern => $handler) {
    // Convert pattern ke regex
    $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);
    $pattern = '#^' . $pattern . '$#';
    
    if (preg_match($pattern, $route, $matches)) {
        $matched = true;
        
        // Extract parameters
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        
        // Jalankan handler
        if (is_callable($handler)) {
            $handler($params);
        } elseif (is_string($handler)) {
            // Include file view
            if (file_exists(__DIR__ . '/' . $handler)) {
                require_once __DIR__ . '/' . $handler;
            } else {
                http_response_code(404);
                echo "File not found: $handler";
            }
        } elseif (is_array($handler)) {
            // Controller@method format
            [$controller, $method] = $handler;
            if (file_exists(__DIR__ . "/app/controllers/$controller.php")) {
                require_once __DIR__ . "/app/controllers/$controller.php";
                $instance = new $controller();
                if (method_exists($instance, $method)) {
                    $instance->$method($params);
                } else {
                    http_response_code(404);
                    echo "Method not found: $method";
                }
            }
        }
        break;
    }
}

// 404 jika tidak ada route yang cocok
if (!$matched) {
    http_response_code(404);
    include __DIR__ . '/app/views/errors/404.php';
}