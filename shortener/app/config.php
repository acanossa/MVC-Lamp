<?php
namespace App;

define('BASE_URL', 'http://localhost:8000/');
define('DB_FILE', __DIR__ . '/../shortener.sqlite');

function getPdo() {
    static $pdo;
    if ($pdo) return $pdo;
    $pdo = new \PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON;');
    $pdo->exec("CREATE TABLE IF NOT EXISTS urls (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT UNIQUE NOT NULL,
        target_url TEXT NOT NULL,
        created_at DATETIME NOT NULL
    );");
    $pdo->exec("CREATE TABLE IF NOT EXISTS accesses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        url_id INTEGER NOT NULL,
        ip TEXT NOT NULL,
        country TEXT,
        user_agent TEXT,
        created_at DATETIME NOT NULL,
        FOREIGN KEY(url_id) REFERENCES urls(id) ON DELETE CASCADE
    );");
    return $pdo;
}

function generateShortCode($length = 6) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

function getClientIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim(end($parts));
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function getCountryFromIp($ip) {
    $api = "http://ipinfo.io/" . urlencode($ip) . "/json";
    $context = stream_context_create(['http' => ['timeout' => 2, 'method' => 'GET', 'header' => "User-Agent: URL-Shortener/1.0\r\n"]]);
    $json = @file_get_contents($api, false, $context);
    if ($json) {
        $data = json_decode($json, true);
        if (!empty($data['country'])) {
            return $data['country'];
        }
    }
    return 'Unknown';
}
