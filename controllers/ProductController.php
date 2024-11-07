<?php

require_once __DIR__ . '/../models/ProductModel.php';

class ProductController {
    private $productModel;

    public function __construct($db) {
        $this->productModel = new ProductModel($db);
    }

    public function getProducts() {
        return json_encode($this->productModel->getProducts());
    }

    public function getProductsByFilter($filter) {
        return json_encode($this->productModel->getProductsByFilter($filter));
    }

    public function getProductsBySubcategory($subcategoryId) {
        return json_encode($this->productModel->getProductsBySubcategory($subcategoryId));
    }

    public function getProductsByIds($productIds) {
        return json_encode($this->productModel->getProductsByIds($productIds));
    }

    public function getAllProducts() {
        $products = $this->productModel->getAllProducts();

        // Convertir las imágenes a Base64 si están presentes
        foreach ($products as &$product) {
            if ($product['image']) {
                // Obtener la imagen desde el servidor y convertirla a Base64
                $imagePath = __DIR__ . '/../uploads/' . $product['image'];
                if (file_exists($imagePath)) {
                    $imageData = file_get_contents($imagePath);
                    $product['image'] = base64_encode($imageData); // Convertir a Base64
                }
            }
        }

        return json_encode($products);
    }

    public function getFilteredProducts($filters) {
        $products = $this->productModel->getFilteredProducts($filters);

        // Convertir las imágenes a Base64 si están presentes
        foreach ($products as &$product) {
            if ($product['image']) {
                // Obtener la imagen desde el servidor y convertirla a Base64
                $imagePath = __DIR__ . '/../uploads/' . $product['image'];
                if (file_exists($imagePath)) {
                    $imageData = file_get_contents($imagePath);
                    $product['image'] = base64_encode($imageData); // Convertir a Base64
                }
            }
        }

        return json_encode($products);
    }

    public function updateProduct($productId, $updatedData) {
        if (isset($updatedData['image']) && is_uploaded_file($updatedData['image'])) {
            // Guardar la imagen como Buffer en la base de datos
            $imageData = file_get_contents($updatedData['image']['tmp_name']);
            $updatedData['image'] = base64_encode($imageData); // Convertir a Base64
        }
        return json_encode($this->productModel->updateProduct($productId, $updatedData));
    }

    public function createProduct($productData) {
        if (isset($productData['image']) && is_uploaded_file($productData['image'])) {
            // Guardar la imagen como Buffer en la base de datos
            $imageData = file_get_contents($productData['image']['tmp_name']);
            $productData['image'] = base64_encode($imageData); // Convertir a Base64
        }
        return json_encode($this->productModel->createProduct($productData));
    }
}

?>
