<?php

class StockModel {

    private $apiKey;
    private $companyKey;

    public function __construct($apiKey, $companyKey) {
        $this->apiKey = $apiKey;
        $this->companyKey = $companyKey;
    }

    public function getStockDetails($stockCodes) {
        $stockCodesParams = http_build_query(['stockCodes' => $stockCodes]);
        $apiUrl = "https://rds.shireburn.cloud:20012/api/SIMS/GetStockDetail?$stockCodesParams&companyKey=$this->companyKey&apikey=$this->apiKey";

        $response = file_get_contents($apiUrl);

        if ($response === false) {
            throw new Exception("Error fetching data from stock API");
        }

        return json_decode($response, true);
    }
}
?>
