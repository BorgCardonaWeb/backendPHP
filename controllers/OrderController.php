<?php
// controllers/OrderController.php

require_once __DIR__ . '/../models/OrderModel.php';

class OrderController {
    private $orderModel;

    public function __construct($db) {
        $this->orderModel = new OrderModel($db);
    }

    public function createOrder() {
        $orderData = json_decode(file_get_contents("php://input"), true);
        try {
            $newOrder = $this->orderModel->createOrder($orderData);
            return $newOrder;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create order: ' . $e->getMessage()]);
        }
    }

    public function getOrdersByClientId($clientId) {
        try {
            $orders = $this->orderModel->getOrdersByClientId($clientId);
            return $orders;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to retrieve orders: ' . $e->getMessage()]);
        }
    }

    public function getOrderById($orderId) {
        try {
            $order = $this->orderModel->getOrderById($orderId);
    
            if (isset($order['arrayProduct'])) {
                $order['arrayProduct'] = json_decode($order['arrayProduct'], true);
            }
    
            return $order;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to retrieve order: ' . $e->getMessage()]);
        }
    }
    

    public function getAllOrders() {
        try {
            $orders = $this->orderModel->getAllOrders();
            return $orders;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to retrieve orders: ' . $e->getMessage()]);
        }
    }

    public function updateOrder($orderId) {
        $updatedData = json_decode(file_get_contents("php://input"), true);
        try {
            $updatedOrder = $this->orderModel->updateOrder($orderId, $updatedData);
            return $updatedOrder;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update order: ' . $e->getMessage()]);
        }
    }

    public function getFilteredOrders() {
        // Decodificar los filtros recibidos en la solicitud
        $filters = json_decode(file_get_contents("php://input"), true);  
    
        try {
            // Obtener las órdenes filtradas desde el modelo
            $orders = $this->orderModel->getFilteredOrders($filters);
            
            // Eliminar valores nulos en el array
            $orders = array_map(function($order) {
                return array_filter($order, function($value) {
                    return $value !== null;
                });
            }, $orders);
    
            // Establecer el encabezado de tipo de contenido como JSON
            header('Content-Type: application/json');
    
            // Devolver las órdenes como JSON sin codificar doblemente
            echo json_encode($orders);
            exit; // Finalizar el script para evitar salida extra
    
        } catch (Exception $e) {
            // En caso de error, devolver un mensaje de error en formato JSON
            http_response_code(500);
            echo json_encode(['message' => 'Failed to retrieve filtered orders: ' . $e->getMessage()]);
            exit;
        }
    }
    
    
    
    
    
    
       
}
?>
