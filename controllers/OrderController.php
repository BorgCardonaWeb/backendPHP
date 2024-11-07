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
            echo json_encode($newOrder);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create order: ' . $e->getMessage()]);
        }
    }

    public function getOrdersByClientId($clientId) {
        try {
            $orders = $this->orderModel->getOrdersByClientId($clientId);
            echo json_encode($orders);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to retrieve orders: ' . $e->getMessage()]);
        }
    }

    public function getOrderById($orderId) {
        try {
            $order = $this->orderModel->getOrderById($orderId);
            echo json_encode($order);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to retrieve order: ' . $e->getMessage()]);
        }
    }

    public function getAllOrders() {
        try {
            $orders = $this->orderModel->getAllOrders();
            echo json_encode($orders);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to retrieve orders: ' . $e->getMessage()]);
        }
    }

    public function updateOrder($orderId) {
        $updatedData = json_decode(file_get_contents("php://input"), true);
        try {
            $updatedOrder = $this->orderModel->updateOrder($orderId, $updatedData);
            echo json_encode($updatedOrder);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update order: ' . $e->getMessage()]);
        }
    }

    public function getFilteredOrders() {
        $filters = json_decode(file_get_contents("php://input"), true);
        try {
            $orders = $this->orderModel->getFilteredOrders($filters);
            echo json_encode($orders);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to retrieve filtered orders: ' . $e->getMessage()]);
        }
    }
}
?>
