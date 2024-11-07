<?php
// controllers/StockController.php

require_once __DIR__ . '/../models/StockModel.php';

class StockController {

    private $apiKey;
    private $companyKey;

    public function __construct($apiKey, $companyKey) {
        $this->apiKey = $apiKey;
        $this->companyKey = $companyKey;
    }

    public function getStockDetails($data) {
        try {
            $stockCodes = $data['stockCodes'] ?? [];

            if (empty($stockCodes) || !is_array($stockCodes)) {
                return ['status' => 400, 'message' => 'stockCodes array is required'];
            }

            $stockDetails = $this->getStockDetailsFromAPI($stockCodes);

            return ['status' => 200, 'data' => $stockDetails];

        } catch (Exception $e) {
            return ['status' => 500, 'message' => 'Failed to fetch stock details: ' . $e->getMessage()];
        }
    }

    private function getStockDetailsFromAPI($stockCodes) {

        $stockCodesParams = implode('&', array_map(function($code, $index) {
            return "stockCodes[{$index}]={$code}";
        }, $stockCodes, array_keys($stockCodes)));

        $apiUrl = "https://rds.shireburn.cloud:20012/api/SIMS/GetStockDetail?{$stockCodesParams}&companyKey={$this->companyKey}&apikey={$this->apiKey}";
        
        $response = file_get_contents($apiUrl);
        return json_decode($response, true);
    }
}

?>
