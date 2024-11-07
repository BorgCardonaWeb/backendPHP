<?php
// routes/api.php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/AuthAdminController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../controllers/ProductController.php';

require_once __DIR__ . '/../config/database.php';

$authController = new AuthController($db);
$authAdminController = new AuthAdminController($db);
$categoryController = new CategoryController($db);
$orderController = new OrderController($db);
$productController = new ProductController($db);

$requestMethod = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace("/gardeningMaltaBackend", "", $path);

if ($requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    switch ($path) {
        // Autenticación de usuario
        case '/auth/register':
            echo json_encode($authController->register($data));
            break;
        case '/auth/login':
            echo json_encode($authController->login($data['email'], $data['password']));
            break;
        case '/auth/forgot-password':
            echo json_encode($authController->forgotPassword($data['email']));
            break;
        case '/auth/reset-password':
            echo json_encode($authController->resetPassword($data['email'], $data['newPassword']));
            break;
        case '/auth/update-user':
            echo json_encode($authController->updateUser($data));
            break;

        // Autenticación de administrador
        case '/admin/auth/register':
            echo json_encode($authAdminController->register($data));
            break;
        case '/admin/auth/login':
            echo json_encode($authAdminController->login($data['email'], $data['password']));
            break;
        case '/admin/auth/forgot-password':
            echo json_encode($authAdminController->forgotPassword($data['email']));
            break;
        case '/admin/auth/reset-password':
            echo json_encode($authAdminController->resetPassword($data['email'], $data['newPassword']));
            break;
        case '/admin/auth/update-user':
            echo json_encode($authAdminController->updateUser($data));
            break;

        // Órdenes
        case '/orders/create':
            echo json_encode($orderController->createOrder($data));
            break;
        case '/orders/filter':
            echo json_encode($orderController->getFilteredOrders($data));
            break;

        // Productos
        case '/products':
            echo json_encode($productController->createProduct($data));
            break;
            case '/products/filterParam':
                echo json_encode($productController->getProductsByFilter($data)); 
                break;
        case '/products/banner':
            echo json_encode($productController->insertImageBanner($data));
            break;

        default:
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
    }
} elseif ($requestMethod === 'GET') {
    switch ($path) {
        // Categorías
        case '/categories':
            echo json_encode($categoryController->getCategories());
            break;

        // Órdenes
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

        // Productos
        case '/products':
            echo json_encode($productController->getProducts()); 
            break;
        case (preg_match('/^\/products\/subcategory\/(\d+)$/', $path, $matches) ? true : false):
            $subcategoryId = $matches[1];
            echo json_encode($productController->getProductsBySubcategory($subcategoryId));
            break;
        case (preg_match('/^\/products\/ids\/(.+)$/', $path, $matches) ? true : false):
            $productIds = explode(',', $matches[1]);
            echo json_encode($productController->getProductsByIds($productIds));
            break;
        case (preg_match('/^\/products\/(\d+)$/', $path, $matches) ? true : false):
            $productId = $matches[1];
            echo json_encode($productController->getProductById($productId));
            break;
        case '/products/banners':
            echo json_encode($productController->getAllBannerImages());
            break;

        default:
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
    }
} elseif ($requestMethod === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    switch ($path) {
        case (preg_match('/^\/products\/(\d+)$/', $path, $matches) ? true : false):
            $productId = $matches[1];
            echo json_encode($productController->updateProduct($productId, $data));
            break;
        default:
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
    }
} elseif ($requestMethod === 'DELETE') {
    switch (true) {
        case (preg_match('/^\/products\/banner\/(\d+)$/', $path, $matches) ? true : false):
            $imageId = $matches[1];
            echo json_encode($productController->deleteImage($imageId));
            break;
        default:
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
}
