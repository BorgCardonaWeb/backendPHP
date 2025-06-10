<?php
// routes/api.php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/AuthAdminController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/EventController.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/StockController.php';

$apiKey = 'd9ffc251-925e-410b-8b41-86a16319585e';
$companyKey = '001';

$stockController = new StockController($apiKey, $companyKey);

$authController = new AuthController($db);
$authAdminController = new AuthAdminController($db);
$categoryController = new CategoryController($db);
$orderController = new OrderController($db);
$productController = new ProductController($db);
$eventController = new EventController($db);

$requestMethod = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace("/gardeningMaltaBackend", "", $path);

if ($requestMethod === 'POST') {
    if ($path === '/products/banner') {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            $data = ['image' => $imageData];
            echo json_encode($productController->insertImageBanner($data));
        } else {
            echo json_encode(['error' => 'No image file uploaded or error during upload']);
        }
    } elseif (preg_match('/^\/products\/banner\/(\d+)$/', $path, $matches)) {
        $productId = $matches[1];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            $data = ['image' => $imageData, 'productId' => $productId];
            echo json_encode($productController->updateProductImage($data));
        } else {
            echo json_encode(['error' => 'No image file uploaded or error during upload']);
        }
    } else {
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
            case '/auth/reset-password':
                echo json_encode($authController->resetPassword($data['token'], $data['newPassword']));
                break;
            case '/auth/update-user':
                echo json_encode($authController->updateUser($data));
                break;

            case '/admin-auth/register':
                echo json_encode($authAdminController->register($data));
                break;
            case '/admin-auth/login':
                echo json_encode($authAdminController->login($data['email'], $data['password']));
                break;
            case '/admin-auth/forgot-password':
                echo json_encode($authAdminController->forgotPassword($data['email']));
                break;
            case '/admin-auth/reset-password':
                echo json_encode($authAdminController->resetPassword($data['token'], $data['newPassword']));
                break;
            case '/admin-auth/update-user':
                echo json_encode($authAdminController->updateUser($data));
                break;

            case '/orders/create':
                echo json_encode($orderController->createOrder($data));
                break;
            case '/orders/filter':
                echo json_encode($orderController->getFilteredOrders($data));
                break;

            case '/products/create':
                echo json_encode($productController->createProduct($data));
                break;
            case '/products/filterParam':
                echo json_encode($productController->getProductsByFilter($data)); 
                break;
            case '/products/byIds':
                echo json_encode($productController->getProductsByIds($data)); 
                break;
            case '/products/filter':
                echo json_encode($productController->getFilteredProducts($data));  
                break;

            case '/stock/getStockDetails':
                echo json_encode($stockController->getStockDetails($data)); 
                break;

            case '/events/create':
                echo json_encode($eventController->createEvent($data));
                break;

            default:
                http_response_code(404);
                echo json_encode(['message' => 'Route not found']);
        }
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
            echo json_encode($productController->getProducts()); 
            break;
        case '/Allproducts':
            echo json_encode($productController->getAllProducts()); 
            break;
        case (preg_match('/^\/products\/subcategory\/(\d+)$/', $path, $matches) ? true : false):
            $subcategoryId = $matches[1];
            echo json_encode($productController->getProductsBySubcategory($subcategoryId));
            break;
        case (preg_match('/^\/products\/(\d+)$/', $path, $matches) ? true : false):
            $productId = $matches[1];
            echo json_encode($productController->getProductById($productId));
            break;
        case '/products/allBannerImages':
            echo json_encode($productController->getAllBannerImages());
            break;
        case (preg_match('/^\/auth\/user\/(\d+)$/', $path, $matches) ? true : false):
            $id = $matches[1];
            echo json_encode($authAdminController->getAdminUserById($id));
            break;

        case '/events':
            echo json_encode($eventController->getEvents());
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
        case (preg_match('/^\/orders\/(\d+)$/', $path, $matches) ? true : false):
            $orderId = $matches[1]; 
            echo json_encode($orderController->updateOrder($orderId, $data));
            break;
        case (preg_match('/^\/auth\/users\/(\d+)$/', $path, $matches) ? true : false):
            $userId = $matches[1];
            echo json_encode($authController->updateUser($userId, $data));
            break;
        case (preg_match('/^\/events\/(\d+)$/', $path, $matches) ? true : false):
            $eventId = $matches[1];
            echo json_encode($eventController->updateEvents($eventId));
            break;

        default:
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
    }
} elseif ($requestMethod === 'DELETE') {
    switch (true) {
        case (preg_match('/^\/products\/banner\/(\d+)$/', $path, $matches) ? true : false):
            $imageId = $matches[1];
            echo json_encode($productController->deleteImageById($imageId)); 
            break;
        case (preg_match('/^\/events\/(\d+)$/', $path, $matches) ? true : false):
            $eventId = $matches[1];
            echo json_encode($eventController->deleteEventById($eventId));
            break;

        default:
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
}
