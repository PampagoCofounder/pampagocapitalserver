<?php

header("Access-Control-Allow-Origin: http://localhost:5173");
//header("Access-Control-Allow-Origin: https://containergo.pampago.site");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS,DELETE,PUT");
header("Content-Type: application/json");

// 🔥 responder preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

?>