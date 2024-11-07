<?php

require_once __DIR__ . '/../models/CategoryModel.php';

class CategoryController
{
    private $categoryModel;

    public function __construct($db)
    {
        $this->categoryModel = new CategoryModel($db);
    }

    public function getCategories()
    {
        try {
            $data = $this->categoryModel->getAllCategories();

            $categories = [];

            foreach ($data as $row) {
           
                $categoryIndex = array_search($row['categoryId'], array_column($categories, 'id'));

             
                if ($categoryIndex === false) {
                    $category = [
                        'id' => $row['categoryId'],
                        'name' => $row['categoryName'],
                        'image' => $row['categoryImage'],
                        'stock' => $row['stock'],
                        'subcategories' => []
                    ];
                    $categories[] = $category;
                    $categoryIndex = array_key_last($categories); 
                }

                if ($row['subcategoryId']) {
                    $subcategory = [
                        'id' => $row['subcategoryId'],
                        'name' => $row['subcategoryName'],
                        'image' => $row['subcategoryImage'],
                        'stock' => $row['subcategoryStock']
                    ];
                    $categories[$categoryIndex]['subcategories'][] = $subcategory;
                }
            }

            header('Content-Type: application/json');
            echo json_encode($categories);

        } catch (Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['message' => 'Failed to fetch categories: ' . $e->getMessage()]);
        }
    }
}
