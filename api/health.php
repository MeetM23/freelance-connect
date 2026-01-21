<?php
// Set headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Set HTTP status code to 200 OK
http_response_code(200);

// Prepare the response data
$response = [
    'status' => 'ok',
    'message' => 'PHP is running successfully on Vercel!',
    'timestamp' => date('c')
];

// Output the JSON response
echo json_encode($response);
