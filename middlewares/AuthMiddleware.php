<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;

function authenticateToken($token) {
    $secret = 'your_jwt_secret';
    try {
        $decoded = JWT::decode($token, $secret, ['HS256']);
        return $decoded;
    } catch (Exception $e) {
        return null;
    }
}
