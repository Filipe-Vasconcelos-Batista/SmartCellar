<?php

namespace App\Services;

use GuzzleHttp\Client;
use Swagger\Client\Api\BarcodeScanApi;
use Swagger\Client\Configuration;
use Swagger\Client\Model\BarcodeScanResult;

class ApiService
{
    private $apiKey;
    private $apiKeyLook;

    public function __construct(string $apiKey, string $apiKeyLookup)
    {
        $this->apiKey = $apiKey;
        $this->apiKeyLook = $apiKeyLookup;
    }
    public function getCode($imageFile): BarcodeScanResult
    {

        // Replace 'YOUR_API_KEY' with your actual API key
        $apiKey = getenv('API_KEY');

        // Initialize the API configuration
        $config = Configuration::getDefaultConfiguration()->setApiKey('Apikey', $this->apiKey);

        // Create an instance of the BarcodeScanApi
        $apiInstance = new BarcodeScanApi(new Client(), $config);

        try {
            // Perform the barcode scan on the uploaded image
            $result = $apiInstance->barcodeScanImage($imageFile->getPathname());

            // Handle the result (e.g., process it further)
            // Replace this with your actual logic
            // ...

            // Return the result (or modify as needed)
            return $result;
        } catch (\Exception $e) {
            // Handle exceptions (e.g., log the exception)
            throw new \RuntimeException('Error during barcode scan: ' . $e->getMessage());
        }
    }
    public function getProduct(string $barcode){
        $url = "https://api.barcodelookup.com/v3/products?barcode=$barcode&formatted=y&key=$this->apiKeyLook";
        $client = new Client();
        $response = $client->get($url);

        // Parse the JSON response
        $data = json_decode($response->getBody(), true);
        var_dump($data);
        $productInfo = [
            'title' => $data['products'][0]['title'],
            'category' => $data['products'][0]['category'],
            'manufacturer' => $data['products'][0]['manufacturer'],
        ];

        return $productInfo;

    }
}
