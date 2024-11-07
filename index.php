<?php
// Asegúrate de que los encabezados CORS se envíen antes de cualquier salida.
header("Access-Control-Allow-Origin: *"); // Permitir todos los orígenes durante las pruebas

$allowedOrigins = [
    "http://localhost:4200",  // Origen de desarrollo local
    "https://gardeningmalta.com",  // Origen de producción
    "https://gardeningmalta.com.mt"  // Origen para otro dominio
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Manejar solicitudes OPTIONS (preflight) correctamente
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("HTTP/1.1 200 OK");
    exit(); // Termina el script después de procesar OPTIONS
}

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
} else {
    header("HTTP/1.1 403 Forbidden");
    exit(); // Termina el script si el origen no es permitido
}

header("Cache-Control: no-cache, no-store, must-revalidate"); // Evitar caché

// El resto del código, como la carga de dependencias y enrutamiento
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/routes/api.php';
?>