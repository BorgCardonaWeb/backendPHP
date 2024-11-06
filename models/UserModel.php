<?php
// models/UserModel.php

require_once __DIR__ . '/../config/database.php';

class UserModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($user) {
        $stmt = $this->db->prepare("
            INSERT INTO users (name, lastName, address, city, postalCode, phoneNumber, email, password, phonePrefix) 
            VALUES (:name, :lastName, :address, :city, :postalCode, :phoneNumber, :email, :password, :phonePrefix)
        ");
        return $stmt->execute($user);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($id, $password) {
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateUser($id, $user) {
        $stmt = $this->db->prepare("
            UPDATE users SET name = :name, lastName = :lastName, address = :address, 
            city = :city, postalCode = :postalCode, phoneNumber = :phoneNumber, 
            email = :email, phonePrefix = :phonePrefix WHERE id = :id
        ");
        $user['id'] = $id;
        return $stmt->execute($user);
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
