<?php

namespace App\Services;

use Swagger\Client\Api\BarcodeScanApi;
use Swagger\Client\Configuration;
use GuzzleHttp\Client;

class BarcodeScanService
{
    private $apiKey;
    public function __construct(){
        $this->apiKey = getenv('API_KEY');
        if (!$this->apiKey) {
            throw new \RuntimeException('API key not set');
        }else {
            error_log('API key: ' . $this->apiKey);
        }
    }
    public function getCode(string $filepath){
        print_r("started barcode scan");
        $config=Configuration::getDefaultConfiguration()->setApiKey('apiKey',$this->apiKey);
        $apiInstance=new BarcodeScanAPI(new Client(), $config);
        try{
            $result=$apiInstance->barcodeScanImage($filepath);
            return $result;
        }catch (\Exception $exception){
            throw new \RuntimeException('Error during the barcode scan: ' . $exception->getMessage());
        }
    }

}