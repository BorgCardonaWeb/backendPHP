<?php

require_once __DIR__ . '/../config/database.php';

class UserAdminModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($user) {
        $stmt = $this->db->prepare("
            INSERT INTO adminusers (name, last_name, email, mobile, password) 
            VALUES (:name, :last_name, :email, :mobile, :password)
        ");
        return $stmt->execute($user);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM adminusers WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($id, $password) {
        $stmt = $this->db->prepare("UPDATE adminusers SET password = :password WHERE id = :id");
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateUser($id, $user) {
        $stmt = $this->db->prepare("
            UPDATE adminusers SET name = :name, last_name = :last_name, email = :email, 
            mobile = :mobile WHERE id = :id
        ");
        $user['id'] = $id;
        return $stmt->execute($user);
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM adminusers WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
