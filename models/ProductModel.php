<?php

require_once __DIR__ . '/../config/database.php';

class ProductModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getProducts() {
        $stmt = $this->db->prepare("SELECT sku, name FROM products WHERE active = TRUE");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductsByFilter($filter) {
        $decodedFilter = urldecode($filter);
        $escapedFilter = str_replace("'", "''", $decodedFilter);
        $filterWithWildcards = "%" . $escapedFilter . "%";

        $stmt = $this->db->prepare("SELECT * FROM products WHERE name LIKE :filter AND active = TRUE");
        $stmt->bindParam(':filter', $filterWithWildcards);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductsBySubcategory($subcategoryId) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE subcategoryId = :subcategoryId AND active = TRUE");
        $stmt->bindParam(':subcategoryId', $subcategoryId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductsByIds($productIds) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $this->db->prepare("SELECT * FROM products WHERE ProductID IN ($placeholders) AND active = TRUE");
        $stmt->execute($productIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllProducts() {
        $stmt = $this->db->prepare("SELECT * FROM products ORDER BY ProductID DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFilteredProducts($filters) {
        $whereClauses = ['active = TRUE'];
        $values = [];

        if (isset($filters['name'])) {
            $whereClauses[] = 'name LIKE ?';
            $values[] = '%' . $filters['name'] . '%';
        }

        if (isset($filters['sku'])) {
            $whereClauses[] = 'sku LIKE ?';
            $values[] = '%' . $filters['sku'] . '%';
        }

        $whereSQL = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
        $stmt = $this->db->prepare("SELECT * FROM products $whereSQL ORDER BY ProductID DESC");
        $stmt->execute($values);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProduct($productId, $updatedData) {
        $stmt = $this->db->prepare(
            "UPDATE products SET name = ?, description = ?, shortDescription = ?, active = ?, image = ? WHERE productID = ?"
        );
        $stmt->execute([
            $updatedData['name'],
            $updatedData['description'],
            $updatedData['shortDescription'],
            $updatedData['active'],
            $updatedData['image'],
            $productId
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception('Product not found or no changes made');
        }

        return array_merge(['productId' => $productId], $updatedData);
    }

    public function createProduct($productData) {
        $stmt = $this->db->prepare(
            "INSERT INTO products (sku, name, description, subcategoryId, shortDescription, image, active) 
            VALUES (?, ?, ?, ?, ?, ?, TRUE)"
        );
        $stmt->execute([
            $productData['sku'],
            $productData['name'],
            $productData['description'],
            $productData['subcategoryId'],
            $productData['shortDescription'],
            $productData['image']
        ]);

        $subcategoryStmt = $this->db->prepare("SELECT stock FROM subcategories WHERE id = ?");
        $subcategoryStmt->execute([$productData['subcategoryId']]);
        $subcategory = $subcategoryStmt->fetch(PDO::FETCH_ASSOC);

        if ($subcategory) {
            $newStock = $subcategory['stock'] + 1;
            $this->db->prepare("UPDATE subcategories SET stock = ? WHERE id = ?")
                     ->execute([$newStock, $productData['subcategoryId']]);
        }

        $categoryStmt = $this->db->prepare(
            "SELECT c.id, s.stock FROM categories c JOIN subcategories s ON c.id = s.categoryId WHERE s.id = ?"
        );
        $categoryStmt->execute([$productData['subcategoryId']]);
        $category = $categoryStmt->fetch(PDO::FETCH_ASSOC);

        if ($category) {
            $newCategoryStock = $category['stock'] + 1;
            $this->db->prepare("UPDATE categories SET stock = ? WHERE id = ?")
                     ->execute([$newCategoryStock, $category['id']]);
        }

        return $stmt->rowCount() > 0;
    }
}

?>
