<?php
require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/controllers/UrlController.php';
require_once __DIR__ . '/app/models/UrlModel.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = App\getPdo();
$model = new App\Models\UrlModel($pdo);
$controller = new App\Controllers\UrlController($model, BASE_URL);
$action = $_GET['action'] ?? '';

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($action === 'create') {
    if ($method !== 'POST') {
        jsonResponse(['error' => 'Método no permitido'], 405);
    }
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $result = $controller->apiCreate($body);
    if (isset($result['error'])) {
        jsonResponse(['error' => $result['error']], $result['code'] ?? 400);
    }
    jsonResponse($result['data'], 201);
}

if ($action === 'urls') {
    $result = $controller->apiUrls();
    jsonResponse($result['data'], $result['code']);
}

if ($action === 'stats') {
    $code = $_GET['code'] ?? '';
    $result = $controller->apiStats($code);
    if (isset($result['error'])) {
        jsonResponse(['error' => $result['error']], $result['code'] ?? 400);
    }
    jsonResponse($result['data'], $result['code']);
}

jsonResponse(['error' => 'Acción API no válida'], 400);
