<?php

namespace App\Services;

use GuzzleHttp\Client;

class ProductLookupService
{
    private $apiKeyLook;
    public function __construct(){
        $this->apiKeyLook = getenv('API_KEY_LOOKUP');
    }
    public function getProduct(string $barcode)
    {
        $url="https://api.barcodelookup.com/v3/products?barcode=$barcode&formatted=y&key=$this->apiKeyLook";
        $client=new Client();
        $response=$client->get($url);

        $data=json_decode($response->getBody(),true);
        $productInfo=[
            'title' => $data['products'][0]['title'],
            'category' => $data['products'][0]['category'],
            'manufacturer' => $data['products'][0]['manufacturer'],
        ];
        return $productInfo;
    }

}