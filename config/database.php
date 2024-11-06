<?php
// config/database.php

// Verificar si el archivo .env existe
if (!file_exists(__DIR__ . '/../.env')) {
    die('El archivo .env no se encuentra.');
}

// Leer el archivo .env
$lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Parsear las variables del archivo .env y almacenarlas en un array
foreach ($lines as $line) {
    if (strpos($line, '#') === 0 || empty($line)) {
        continue; // Ignorar comentarios y líneas vacías
    }

    list($key, $value) = explode('=', $line, 2);
    putenv(trim($key) . '=' . trim($value)); // Establecer la variable de entorno
}

// Ahora intentamos usar las variables de entorno cargadas manualmente
try {
    $db = new PDO(
        "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8", 
        getenv('DB_USER'), 
        getenv('DB_PASSWORD')
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Si hay un error, muestra el mensaje de error
    die("Database connection failed: " . $e->getMessage());
}
?>
