<?php
function send_json($data, $status = 200)
{
    header("Content-Type: application/json");
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function handle_cors()
{
    $allowed_origin = getenv('FRONTEND_URL');

    if ($allowed_origin && isset($_SERVER['HTTP_ORIGIN'])) {
        // Only allow the specific origin
        if ($_SERVER['HTTP_ORIGIN'] === $allowed_origin) {
            header("Access-Control-Allow-Origin: {$allowed_origin}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400'); // cache for 1 day
        }
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        }

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }

        exit(0);
    }
}
