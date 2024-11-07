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

    public function updateProduct($request, $response, $args) {
        try {
            $productId = $args['productId'];
            $updatedData = $request->getParsedBody();

            $updatedData['active'] = isset($updatedData['active']) && $updatedData['active'] === '1' ? 1 : 0;

            if ($request->getUploadedFiles() && isset($request->getUploadedFiles()['image'])) {
                $imageFile = $request->getUploadedFiles()['image'];
                if ($imageFile->getError() === UPLOAD_ERR_OK) {
                    $updatedData['image'] = file_get_contents($imageFile->file);
                }
            }

            $updatedProduct = $this->productModel->updateProduct($productId, $updatedData);
            return $response->withJson($updatedProduct);
        } catch (Exception $e) {
            return $response->withStatus(500)->write('Failed to update product: ' . $e->getMessage());
        }
    }

    public function createProduct($request, $response) {
        try {
            $productData = $request->getParsedBody();

            if ($request->getUploadedFiles() && isset($request->getUploadedFiles()['image'])) {
                $imageFile = $request->getUploadedFiles()['image'];
                if ($imageFile->getError() === UPLOAD_ERR_OK) {
                    $productData['image'] = file_get_contents($imageFile->file);
                }
            }

            $newProduct = $this->productModel->createProduct($productData);
            return $response->withStatus(201)->withJson($newProduct);
        } catch (Exception $e) {
            return $response->withStatus(500)->write('Failed to create product: ' . $e->getMessage());
        }
    }

    public function insertImageBanner($request, $response) {
        try {
            $imgData = $request->getParsedBody();

            if ($request->getUploadedFiles() && isset($request->getUploadedFiles()['image'])) {
                $imageFile = $request->getUploadedFiles()['image'];
                if ($imageFile->getError() === UPLOAD_ERR_OK) {
                    $imgData['image'] = file_get_contents($imageFile->file);
                }
            }

            $newImage = $this->productModel->insertImageBanner($imgData);
            return $response->withStatus(201)->withJson($newImage);
        } catch (Exception $e) {
            return $response->withStatus(500)->write('Failed to insert image: ' . $e->getMessage());
        }
    }

    public function getAllBannerImages($request, $response) {
        try {
            $images = $this->productModel->getAllBannerImages();

            $processedImages = array_map(function($data) {
                if ($data['image']) {
                    $data['image'] = base64_encode($data['image']);
                }
                return $data;
            }, $images);

            return $response->withJson($processedImages);
        } catch (Exception $e) {
            return $response->withStatus(500)->write('Failed to fetch banner images: ' . $e->getMessage());
        }
    }

    public function deleteImage($request, $response, $args) {
        try {
            $imageId = $args['imageId'];
            $result = $this->productModel->deleteImageById($imageId);
            return $response->withJson($result);
        } catch (Exception $e) {
            return $response->withStatus(500)->write('Failed to delete image: ' . $e->getMessage());
        }
    }
}
