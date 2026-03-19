<?php
namespace App\Controllers;

use App\Models\UrlModel;

class UrlController {
    private $model;
    private $baseUrl;

    public function __construct(UrlModel $model, $baseUrl) {
        $this->model = $model;
        $this->baseUrl = $baseUrl;
    }

    public function home() {
        include __DIR__ . '/../views/home.php';
    }

    public function redirect($code) {
        $url = $this->model->getUrlByCode($code);
        if (!$url) {
            http_response_code(404);
            echo 'URL corto no encontrado.';
            exit;
        }
        $ip = \App\getClientIp();
        $country = \App\getCountryFromIp($ip);
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $this->model->logAccess($url['id'], $ip, $country, $ua);
        header('Location: ' . $url['target_url'], true, 302);
        exit;
    }

    public function apiCreate($payload) {
        $url = trim($payload['url'] ?? '');
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return ['error' => 'URL inválida (debe iniciar con http:// o https://)', 'code' => 400];
        }
        $code = \App\generateShortCode(6);
        while ($this->model->getUrlByCode($code)) {
            $code = \App\generateShortCode(6);
        }
        $id = $this->model->createUrl($code, $url);
        return ['code' => 201, 'data' => ['code' => $code, 'short_url' => $this->baseUrl . 'redirect.php?c=' . $code, 'target_url' => $url, 'id' => $id]];
    }

    public function apiUrls() {
        $rows = $this->model->getAllUrls();
        return ['code' => 200, 'data' => ['urls' => $rows]];
    }

    public function apiStats($code) {
        $code = trim($code ?? '');
        if (empty($code)) {
            return ['error' => 'Falta código', 'code' => 400];
        }
        $url = $this->model->getUrlByCode($code);
        if (!$url) {
            return ['error' => 'URL no encontrada', 'code' => 404];
        }
        $total = $this->model->getAccessTotal($url['id']);
        $countries = $this->model->getCountries($url['id']);
        $byDay = $this->model->getAccessByDay($url['id']);
        return ['code' => 200, 'data' => ['code' => $code, 'target_url' => $url['target_url'], 'created_at' => $url['created_at'], 'total_accesses' => $total, 'countries' => $countries, 'days' => array_column($byDay, 'day'), 'hits' => array_column($byDay, 'hits')]];
    }
}
