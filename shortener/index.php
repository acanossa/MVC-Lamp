<?php
require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/controllers/UrlController.php';
require_once __DIR__ . '/app/models/UrlModel.php';

$pdo = App\getPdo();
$model = new App\Models\UrlModel($pdo);
$controller = new App\Controllers\UrlController($model, BASE_URL);
$controller->home();

