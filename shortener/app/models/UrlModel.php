<?php
namespace App\Models;

class UrlModel {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createUrl($code, $targetUrl) {
        $stmt = $this->pdo->prepare('INSERT INTO urls (code, target_url, created_at) VALUES (?, ?, datetime("now"))');
        $stmt->execute([$code, $targetUrl]);
        return $this->pdo->lastInsertId();
    }

    public function getUrlByCode($code) {
        $stmt = $this->pdo->prepare('SELECT id, target_url, created_at FROM urls WHERE code = ?');
        $stmt->execute([$code]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAllUrls() {
        return $this->pdo->query('SELECT u.id, u.code, u.target_url, u.created_at, (SELECT COUNT(*) FROM accesses a WHERE a.url_id = u.id) AS total_accesses FROM urls u ORDER BY u.id DESC')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function logAccess($urlId, $ip, $country, $userAgent) {
        $stmt = $this->pdo->prepare('INSERT INTO accesses (url_id, ip, country, user_agent, created_at) VALUES (?, ?, ?, ?, datetime("now"))');
        $stmt->execute([$urlId, $ip, $country, $userAgent]);
    }

    public function getAccessTotal($urlId) {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM accesses WHERE url_id = ?');
        $stmt->execute([$urlId]);
        return (int)$stmt->fetchColumn();
    }

    public function getCountries($urlId) {
        $stmt = $this->pdo->prepare('SELECT country, COUNT(*) AS hits FROM accesses WHERE url_id = ? GROUP BY country ORDER BY hits DESC');
        $stmt->execute([$urlId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAccessByDay($urlId) {
        $stmt = $this->pdo->prepare('SELECT DATE(created_at) AS day, COUNT(*) AS hits FROM accesses WHERE url_id = ? GROUP BY DATE(created_at) ORDER BY day ASC');
        $stmt->execute([$urlId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
