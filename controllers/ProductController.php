<?php

require_once __DIR__ . '/../models/ProductModel.php';

class ProductController {
    private $productModel;

    public function __construct($db) {
        $this->productModel = new ProductModel($db);
    }

    public function getProducts() {
        try {
            $products = $this->productModel->getProducts();
    
            $processedProducts = array_map(function($product) {
                if (isset($product['image']) && $product['image']) {
                    $product['image'] = base64_encode($product['image']);
                }
                return $product;
            }, $products);
    
            return $processedProducts;
        } catch (Exception $error) {
            error_log("Error fetching products: " . $error->getMessage());
            return ['error' => 'Failed to fetch products: ' . $error->getMessage()];
        }
    }

    public function getAllProducts() {
        try {
            $products = $this->productModel->getAllProducts();
    
            $processedProducts = array_map(function($product) {
                if (isset($product['image']) && $product['image']) {
                    $product['image'] = base64_encode($product['image']);
                }
                return $product;
            }, $products);
    
            return $processedProducts;
        } catch (Exception $error) {
            error_log("Error fetching products: " . $error->getMessage());
            return ['error' => 'Failed to fetch products: ' . $error->getMessage()];
        }
    }

    public function getAllBannerImages() {
        try {
            // Obtiene las imágenes desde la base de datos
            $images = $this->productModel->getAllBannerImages();
    
            // Procesa cada imagen convirtiéndola de binario a Base64
            $processedImages = array_map(function($data) {
                if ($data['image']) {
                    // Convierte el binario a Base64
                    $data['image'] = base64_encode($data['image']);
                }
                return $data;
            }, $images);
    
            // Configura el encabezado para JSON y retorna la respuesta
            header("Content-Type: application/json");
            echo json_encode($processedImages);
            exit; // Termina el script después de enviar la respuesta
    
        } catch (Exception $e) {
            // Retorna un error en formato JSON también
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch banner images: ' . $e->getMessage()]);
            exit;
        }
    }
    

    
public function getProductsByFilter($data) {
    try {
        $filter = $data['filter'] ?? '';  

        if (empty($filter)) {
            return ['status' => 400, 'message' => 'Filter parameter is required'];
        }

        $products = $this->productModel->getProductsByFilter($filter);

        $processedProducts = array_map(function($product) {
            if ($product['image']) {
                $product['image'] = base64_encode($product['image']);
            }
            return $product;
        }, $products);

        return ['status' => 200, 'products' => $processedProducts]; 
    } catch (Exception $e) {
        return ['status' => 500, 'message' => 'Failed to fetch filtered products: ' . $e->getMessage()];
    }
}
    

    public function getProductsBySubcategory($subcategoryId) {
        try {
            $products = $this->productModel->getProductsBySubcategory($subcategoryId);
    
            $processedProducts = array_map(function($product) {
                if ($product['image']) {
                    $product['image'] = base64_encode($product['image']);
                }
                return $product;
            }, $products);
    
            return $processedProducts; 
        } catch (Exception $e) {
            return ['error' => 'Failed to fetch products by subcategory: ' . $e->getMessage()];
        }
    }
    
    public function getProductsByIds($data) {
        try {
            $productIds = $data['productIds'] ?? []; 
    
            if (!is_array($productIds) || empty($productIds)) {
                return ['status' => 400, 'message' => 'Product IDs array is required and should not be empty'];
            }
    
            $products = $this->productModel->getProductsByIds($productIds);
    
            $processedProducts = array_map(function($product) {
                if ($product['image']) {
                    $product['image'] = base64_encode($product['image']);
                }
                return $product;
            }, $products);
    
            return $processedProducts;
        } catch (Exception $e) {
            return ['status' => 500, 'message' => 'Failed to fetch products by IDs: ' . $e->getMessage()];
        }
    }


    public function getFilteredProducts($data) {
        try {
            $filters = $data;
    
            if (empty($filters)) {
                return ['status' => 400, 'message' => 'Filters are required'];
            }
    
            $products = $this->productModel->getFilteredProducts($filters);
    
            $processedProducts = array_map(function($product) {
                if ($product['image']) {
                    $product['image'] = base64_encode($product['image']);
                }
                return $product;
            }, $products);
    
            return $processedProducts;
        } catch (Exception $e) {
            return ['status' => 500, 'message' => 'Failed to retrieve filtered products: ' . $e->getMessage()];
        }
    }

    public function updateProduct($productId) {
        $updatedData = json_decode(file_get_contents("php://input"), true);
    
        error_log(print_r($updatedData, true)); 
    
        if ($updatedData === null) {
            return ['success' => false, 'message' => 'Invalid or empty data received'];
        }
    
        try {
            $updatedOrder = $this->productModel->updateProduct($productId, $updatedData);
            return $updatedOrder;
        } catch (Exception $e) {
            http_response_code(500);
            return ['success' => false, 'message' => 'Failed to update product: ' . $e->getMessage()];
        }
    }
    
    public function createProduct($data) {
        try {
            return $this->productModel->createProduct($data); 
        } catch (Exception $e) {
            return $response->withStatus(500)->write('Failed to create product: ' . $e->getMessage());
        }
    }

    public function insertImageBanner($data) {
        try {
            // Verifica si los datos de la imagen se están enviando correctamente como binarios
            if (isset($data['image']) && is_string($data['image'])) {
                // La imagen se envía como un binario en un string, se puede pasar al modelo
                return $this->productModel->insertImageBanner($data); 
            } else {
                throw new Exception('No image data or incorrect format.');
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function updateProductImage($data) {
        try {
            // Verifica si los datos de la imagen se están enviando correctamente como binarios
            if (isset($data['image']) && is_string($data['image'])) {
                // La imagen se envía como un binario en un string, se puede pasar al modelo
                return $this->productModel->updateProductImage($data); 
            } else {
                throw new Exception('No image data or incorrect format.');
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function deleteImageById($imageId) {
        $result = $this->productModel->deleteImageById($imageId);
        return $result;
    }
}
