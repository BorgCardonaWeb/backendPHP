<?php

if (!file_exists(__DIR__ . '/../.env')) {
    die('El archivo .env no se encuentra.');
}

$lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);


foreach ($lines as $line) {
    if (strpos($line, '#') === 0 || empty($line)) {
        continue; 
    }

    list($key, $value) = explode('=', $line, 2);
    putenv(trim($key) . '=' . trim($value)); 
}


try {
    $db = new PDO(
        "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8", 
        getenv('DB_USER'), 
        getenv('DB_PASSWORD')
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
