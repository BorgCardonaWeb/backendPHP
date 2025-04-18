<?php

require_once __DIR__ . '/../config/database.php';

class ProductModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getProducts() {
        try {
            $stmt = $this->db->prepare("SELECT sku, name FROM products WHERE active = TRUE");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch products: ' . $e->getMessage());
        }
    }

    public function getProductsByFilter($filter) {
        try {
            $filter = trim($filter);
            $escapedFilter = '%' . str_replace("'", "''", urldecode($filter)) . '%';
            $stmt = $this->db->prepare("SELECT * FROM products WHERE (name LIKE ? OR sku LIKE ?) AND active = TRUE");
            $stmt->execute([$escapedFilter, $escapedFilter]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch products by filter: ' . $e->getMessage());
        }
    }

    public function getProductsBySubcategory($subcategoryId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE subcategoryId = ? AND active = TRUE");
            $stmt->execute([$subcategoryId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch products by subcategory: ' . $e->getMessage());
        }
    }

    public function getProductsByIds($productIds) {
        try {
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $stmt = $this->db->prepare("SELECT * FROM products WHERE ProductID IN ($placeholders) AND active = TRUE");
            $stmt->execute($productIds);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch products by IDs: ' . $e->getMessage());
        }
    }

    public function getAllProducts() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM products ORDER BY ProductID DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch all products: ' . $e->getMessage());
        }
    }

    public function getFilteredProducts($filters) {
        try {
            $whereClauses = ['active = TRUE'];
            $values = [];

            if (!empty($filters['name'])) {
                $whereClauses[] = "name LIKE ?";
                $values[] = '%' . $filters['name'] . '%';
            }

            if (!empty($filters['sku'])) {
                $whereClauses[] = "sku LIKE ?";
                $values[] = '%' . $filters['sku'] . '%';
            }

            $whereSQL = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
            $stmt = $this->db->prepare("SELECT * FROM products $whereSQL ORDER BY ProductID DESC");
            $stmt->execute($values);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch filtered products: ' . $e->getMessage());
        }
    }

    public function updateProduct($productId, $updatedData) {
        if (!isset($updatedData['name'], $updatedData['description'], $updatedData['shortDescription'], $updatedData['active'])) {
            throw new Exception('Missing required fields');
        }
    
        try {

            $stmt = $this->db->prepare("UPDATE products SET name = ?, description = ?, shortDescription = ?, active = ? WHERE ProductID = ?");

            $stmt->execute([
                $updatedData['name'],
                $updatedData['description'],
                $updatedData['shortDescription'],
                $updatedData['active'],
                $productId
            ]);
    
            if ($stmt->rowCount() === 0) {
                throw new Exception('Product not found or no changes made');
            }
    
            return ['success' => true, 'message' => 'Product updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update product: ' . $e->getMessage()];
        }
    }
    
    

    public function createProduct($productData) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO products (sku, name, description, subcategoryId, shortDescription, image, active) VALUES (?, ?, ?, ?, ?, ?, TRUE)");
            $stmt->execute([
                $productData['sku'],
                $productData['name'],
                $productData['description'],
                $productData['subcategoryId'],
                $productData['shortDescription'],
                $productData['image']
            ]);
            $productId = $this->db->lastInsertId();

            // Actualizar el stock en subcategories
            $stmt = $this->db->prepare("SELECT stock FROM subcategories WHERE id = ?");
            $stmt->execute([$productData['subcategoryId']]);
            $subcategory = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($subcategory) {
                $newStock = $subcategory['stock'] + 1;
                $stmt = $this->db->prepare("UPDATE subcategories SET stock = ? WHERE id = ?");
                $stmt->execute([$newStock, $productData['subcategoryId']]);
            }

            // Actualizar el stock en categories
            $stmt = $this->db->prepare("SELECT c.id, s.stock FROM categories c JOIN subcategories s ON c.id = s.categoryId WHERE s.id = ?");
            $stmt->execute([$productData['subcategoryId']]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($category) {
                $newCategoryStock = $category['stock'] + 1;
                $stmt = $this->db->prepare("UPDATE categories SET stock = ? WHERE id = ?");
                $stmt->execute([$newCategoryStock, $category['id']]);
            }

            $this->db->commit();
            return ['productId' => $productId] + $productData;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Failed to create product: ' . $e->getMessage());
        }
    }

    public function insertImageBanner($imageData) {
        try {
            // Preparar la consulta SQL para insertar la imagen
            $stmt = $this->db->prepare("INSERT INTO images (image) VALUES (?)");

            // Ejecutar la consulta con los datos recibidos
            $stmt->execute([$imageData['image']]);

            // Retornar el ID de la imagen recién insertada junto con los datos
            return ['id' => $this->db->lastInsertId()] + $imageData;
        } catch (Exception $e) {
            // Si ocurre un error, lanzar una excepción con el mensaje de error
            throw new Exception('Failed to create image banner: ' . $e->getMessage());
        }
    }

    public function updateProductImage($imageData) {
        try {
            $stmt = $this->db->prepare("UPDATE products SET image = ? WHERE ProductID = ?");
    
            $stmt->execute([$imageData['image'], $imageData['productId']]);
    
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Product image updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Product not found or no changes made'];
            }
        } catch (Exception $e) {
            throw new Exception('Failed to update product image: ' . $e->getMessage());
        }
    }
    

    public function getAllBannerImages() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM images");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch banner images: ' . $e->getMessage());
        }
    }

    public function deleteImageById($imageId) {
        try {
            // Preparar la consulta SQL para eliminar la imagen
            $stmt = $this->db->prepare("DELETE FROM images WHERE id = ?");
            $stmt->execute([$imageId]);

            // Verificar si se eliminó correctamente
            if ($stmt->rowCount() > 0) {
                return ['message' => 'Image deleted successfully'];
            } else {
                return ['error' => 'Image not found'];
            }
        } catch (Exception $e) {
            // Si ocurre un error, lanzar una excepción con el mensaje de error
            throw new Exception('Failed to delete image: ' . $e->getMessage());
        }
    }
}
