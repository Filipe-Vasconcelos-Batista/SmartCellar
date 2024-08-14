<?php

namespace App\Services;

use GuzzleHttp\Client;

class ProductLookupService
{
    private String $apiKeyLook;
    public function __construct(){
        $this->apiKeyLook = $_ENV['API_KEY_LOOKUP'];
    }
    public function getProduct(string $barcode): array
    {
        $url="https://api.barcodelookup.com/v3/products?barcode=$barcode&formatted=y&key=$this->apiKeyLook";
        $client=new Client();
        try {
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);
            return [
                'title' => $data['products'][0]['title'],
                'category' => $data['products'][0]['category'],
                'manufacturer' => $data['products'][0]['manufacturer'],
            ];
        }catch (\Exception $e){
            return [
                'title' => "",
                'category' => "",
                'manufacturer' => "",
            ];
        }
    }

}