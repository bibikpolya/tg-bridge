<?php
// Получаем метод API из пути (например, /botTOKEN/getMe)
$uri = $_SERVER['REQUEST_URI'];

// Убираем лишние слэши и гейты, если они есть
$cleanPath = ltrim($uri, '/');

if (empty($cleanPath)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(["ok" => false, "description" => "No Telegram method specified"]);
    exit;
}

// Перенаправляем всё на официальный Telegram API
$telegramUrl = "https://api.telegram.org/" . $cleanPath;

$input = file_get_contents('php://input');
$ch = curl_init($telegramUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

// Если был POST-запрос, проксируем его тело
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
    
    // Передаем правильные заголовки типа контента
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
