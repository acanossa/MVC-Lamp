<?php
require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/controllers/UrlController.php';
require_once __DIR__ . '/app/models/UrlModel.php';

$code = trim($_GET['c'] ?? '');
if (empty($code)) {
    http_response_code(400);
    echo 'Código de URL corto faltante.';
    exit;
}

$pdo = App\getPdo();
$model = new App\Models\UrlModel($pdo);
$controller = new App\Controllers\UrlController($model, BASE_URL);
$controller->redirect($code);

