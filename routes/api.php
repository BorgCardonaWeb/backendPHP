<?php
// routes/api.php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/AuthAdminController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../controllers/ProductController.php'; // Agregado el controlador de productos

require_once __DIR__ . '/../config/database.php';

$authController = new AuthController($db);
$authAdminController = new AuthAdminController($db);
$categoryController = new CategoryController($db);
$orderController = new OrderController($db);
$productController = new ProductController($db); // Instancia del controlador de productos

$requestMethod = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace("/gardeningMaltaBackend", "", $path);

if ($requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    switch ($path) {
        case '/auth/register':
            echo json_encode($authController->register($data));
            break;
        case '/auth/login':
            echo json_encode($authController->login($data['email'], $data['password']));
            break;
        case '/auth/forgot-password':
            echo json_encode($authController->forgotPassword($data['email']));
            break;
        case '/admin/register':
            echo json_encode($authAdminController->register($data));
            break;
        case '/admin/login':
            echo json_encode($authAdminController->login($data['email'], $data['password']));
            break;
        case '/admin/forgot-password':
            echo json_encode($authAdminController->forgotPassword($data['email']));
            break;
        case '/orders':
            echo json_encode($orderController->createOrder($data));
            break;
        case '/orders/filter':
            echo json_encode($orderController->getFilteredOrders($data));
            break;
        case '/products':
            echo json_encode($productController->createProduct($data)); // Ruta para crear productos
            break;
        case '/products/filter':
            echo json_encode($productController->getFilteredProducts($data)); // Ruta para obtener productos filtrados
            break;
        default:
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
    }
} elseif ($requestMethod === 'GET') {
    switch ($path) {
        case '/categories':
            echo json_encode($categoryController->getCategories());
            break;
        case '/orders':
            echo json_encode($orderController->getAllOrders());
            break;
        case (preg_match('/^\/orders\/client\/(\d+)$/', $path, $matches) ? true : false):
            $clientId = $matches[1];
            echo json_encode($orderController->getOrdersByClientId($clientId));
            break;
        case (preg_match('/^\/orders\/(\d+)$/', $path, $matches) ? true : false):
            $orderId = $matches[1];
            echo json_encode($orderController->getOrderById($orderId));
            break;
        case '/products':
            echo json_encode($productController->getProducts()); // Ruta para obtener todos los productos
            break;
        case (preg_match('/^\/products\/filter\/(.+)$/', $path, $matches) ? true : false):
            $filter = $matches[1];
            echo json_encode($productController->getProductsByFilter($filter)); // Ruta para obtener productos filtrados por nombre
            break;
        case (preg_match('/^\/products\/subcategory\/(\d+)$/', $path, $matches) ? true : false):
            $subcategoryId = $matches[1];
            echo json_encode($productController->getProductsBySubcategory($subcategoryId)); // Ruta para obtener productos por subcategoría
            break;
        case (preg_match('/^\/products\/ids\/(.+)$/', $path, $matches) ? true : false):
            $productIds = explode(',', $matches[1]);
            echo json_encode($productController->getProductsByIds($productIds)); // Ruta para obtener productos por IDs
            break;
        case (preg_match('/^\/products\/(\d+)$/', $path, $matches) ? true : false):
            $productId = $matches[1];
            echo json_encode($productController->getProductById($productId)); // Ruta para obtener producto por ID
            break;
        case (preg_match('/^\/products\/name\/(.+)$/', $path, $matches) ? true : false):
            $productName = $matches[1];
            echo json_encode($productController->getProductByName($productName)); // Ruta para obtener producto por nombre
            break;
        default:
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
    }
} elseif ($requestMethod === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    switch ($path) {
        case (preg_match('/^\/orders\/(\d+)$/', $path, $matches) ? true : false):
            $orderId = $matches[1];
            echo json_encode($orderController->updateOrder($orderId, $data));
            break;
        case (preg_match('/^\/products\/(\d+)$/', $path, $matches) ? true : false):
            $productId = $matches[1];
            echo json_encode($productController->updateProduct($productId, $data)); // Ruta para actualizar un producto
            break;
        default:
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
}
