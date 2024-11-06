<?php
// routes/api.php

// Incluir el archivo del controlador de autenticación
require_once __DIR__ . '/../controllers/AuthController.php';

// Incluir la configuración de la base de datos
require_once __DIR__ . '/../config/database.php';

// Crear una instancia del controlador de autenticación
$authController = new AuthController($db);

// Verificar el método de la solicitud
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Obtener la ruta de la solicitud
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Comprobar que el método sea POST
if ($requestMethod === 'POST') {
    // Eliminar la parte de la URL que no corresponde a la ruta
    $path = str_replace("/gardeningMaltaBackend", "", $path); // Ajuste para Laragon

    // Definir las rutas para los diferentes métodos
    switch ($path) {
        case '/register':
            $userData = json_decode(file_get_contents('php://input'), true);
            echo json_encode($authController->register($userData));
            break;

        case '/login':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($authController->login($data['email'], $data['password']));
            break;

        case '/forgot-password':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($authController->forgotPassword($data['email']));
            break;

        default:
            // Si la ruta no existe, devolver error 404
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
    }
} else {
    // Si el método no es POST, devolver error 405
    http_response_code(405); // Método no permitido
    echo json_encode(['message' => 'Method not allowed']);
}
?>
