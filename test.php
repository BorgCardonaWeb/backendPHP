<?php
// test.php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo 'DB_HOST: ' . getenv('DB_HOST') . '<br>';
echo 'DB_USER: ' . getenv('DB_USER') . '<br>';
echo 'DB_PASSWORD: ' . getenv('DB_PASSWORD') . '<br>';
echo 'DB_NAME: ' . getenv('DB_NAME') . '<br>';
?>
