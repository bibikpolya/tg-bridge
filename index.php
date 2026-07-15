<?php
// Берем путь запроса, например /bot12345:AAAAAA/getMe
$uri = $_SERVER['REQUEST_URI'];

// Убираем лишние слэши в начале
$cleanPath = ltrim($uri, '/');

if (empty($cleanPath)) {
    header("HTTP/1.1 200 OK");
    echo json_encode(["ok" => true, "status" => "Telegram Bridge is running!"]);
    exit;
}

// Перенаправляем реальный запрос на Telegram
$telegramUrl = "https://api.telegram.org/" . $cleanPath;

$input = file_get_contents('php://input');
$ch = curl_init($telegramUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

// Проксируем POST-данные
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
    
    if (isset($_SERVER['CONTENT_TYPE'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: " . $_SERVER['CONTENT_TYPE']]);
    }
}

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($httpCode);
header('Content-Type: application/json');
echo $response;
