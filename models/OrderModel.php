<?php
// models/OrderModel.php

class OrderModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createOrder($orderData) {
        $sql = "INSERT INTO orders (ClientID, arrayProduct, Date, Status, PaymentType, Island, DeliveryAddress, City, PostalCode, Prefix, Phonenumber, DeliveryNote, Amount, ExtraCostDeliveryGozo, TotalAmount, GeneralNotes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $orderData['ClientID'],
            json_encode($orderData['arrayProduct']),
            $orderData['Date'],
            $orderData['Status'],
            $orderData['PaymentType'],
            $orderData['Island'],
            $orderData['DeliveryAddress'],
            $orderData['City'],
            $orderData['PostalCode'],
            $orderData['Prefix'],
            $orderData['Phonenumber'],
            $orderData['DeliveryNote'],
            $orderData['Amount'],
            $orderData['ExtraCostDeliveryGozo'],
            $orderData['TotalAmount'],
            $orderData['GeneralNotes']
        ]);

        $orderId = $this->db->lastInsertId();
        if (!$orderId) {
            throw new Exception('Order creation failed, no ID returned');
        }

        return $this->getOrderById($orderId);
    }

    public function getOrdersByClientId($clientId) {
        $sql = "SELECT * FROM orders WHERE ClientID = ? ORDER BY Date DESC, OrderID DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderById($orderId) {
        $sql = "SELECT * FROM orders WHERE OrderID = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new Exception('Order not found');
        }

        return $order;
    }

    public function getAllOrders() {
        $sql = "SELECT * FROM orders ORDER BY Date DESC, OrderID DESC LIMIT 100";
        $stmt = $this->db->query($sql);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$orders) {
            throw new Exception('No orders found');
        }

        return $orders;
    }

    public function updateOrder($orderId, $updatedData) {
        $sql = "UPDATE orders SET GeneralNotes = ?, Status = ? WHERE OrderID = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$updatedData['GeneralNotes'], $updatedData['Status'], $orderId]);

        if ($stmt->rowCount() === 0) {
            throw new Exception('Order not found or no changes made');
        }

        return $this->getOrderById($orderId);
    }

    public function getFilteredOrders($filters) {
        $whereClauses = [];
        $values = [];

        if (isset($filters['status'])) {
            $whereClauses[] = "Status = ?";
            $values[] = $filters['status'];
        }
        if (isset($filters['paymentType'])) {
            $whereClauses[] = "PaymentType = ?";
            $values[] = $filters['paymentType'];
        }
        if (isset($filters['date'])) {
            $whereClauses[] = "Date = ?";
            $values[] = $filters['date'];
        }
        if (isset($filters['id'])) {
            $whereClauses[] = "OrderID LIKE ?";
            $values[] = "%" . $filters['id'] . "%";
        }

        $whereSQL = $whereClauses ? "WHERE " . implode(" AND ", $whereClauses) : "";
        $sql = "SELECT * FROM orders $whereSQL ORDER BY Date DESC, OrderID DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
