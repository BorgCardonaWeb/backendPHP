<?php
require_once __DIR__ . '/../config/database.php';
class CategoryModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllCategories()
    {
        try {
            $query = "
                SELECT 
                    c.id AS categoryId, 
                    c.name AS categoryName, 
                    c.image AS categoryImage, 
                    c.stock,
                    s.id AS subcategoryId, 
                    s.name AS subcategoryName, 
                    s.image AS subcategoryImage, 
                    s.stock AS subcategoryStock 
                FROM categories c
                LEFT JOIN subcategories s ON c.id = s.categoryId
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error fetching categories from the database: " . $e->getMessage());
            throw new Exception('Failed to fetch categories from the database: ' . $e->getMessage());
        }
    }
}
