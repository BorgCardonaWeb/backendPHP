<?php

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

    public function updatePassword($userId, $password)
    {
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    public function updateUser($userId, $updatedData)
    {
        // Validar si todos los campos requeridos están presentes
        if (!isset($updatedData['name'], $updatedData['lastName'], $updatedData['address'], 
                  $updatedData['city'], $updatedData['postalCode'], $updatedData['phoneNumber'], 
                  $updatedData['email'], $updatedData['phonePrefix'])) {
            throw new Exception('Missing required fields');
        }
    
        try {
            // Prepara la consulta de actualización con parámetros posicionales
            $stmt = $this->db->prepare("UPDATE users SET name = ?, lastName = ?, address = ?, 
                city = ?, postalCode = ?, phoneNumber = ?, email = ?, phonePrefix = ? WHERE id = ?");
    
            // Ejecuta la consulta con los valores de $updatedData y el $userId
            $stmt->execute([
                $updatedData['name'],
                $updatedData['lastName'],
                $updatedData['address'],
                $updatedData['city'],
                $updatedData['postalCode'],
                $updatedData['phoneNumber'],
                $updatedData['email'],
                $updatedData['phonePrefix'],
                $userId  // El ID del usuario para la condición WHERE
            ]);
    
            // Verifica si alguna fila fue actualizada
            if ($stmt->rowCount() === 0) {
                throw new Exception('User not found or no changes made');
            }
    
            // Retorna un mensaje de éxito si todo salió bien
            return ['success' => true, 'message' => 'User updated successfully'];
        } catch (Exception $e) {
            // Retorna un mensaje de error si algo falla
            return ['success' => false, 'message' => 'Failed to update user: ' . $e->getMessage()];
        }
    }
    

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
