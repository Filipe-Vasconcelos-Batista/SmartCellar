<?php

namespace App\Services;

use Swagger\Client\Api\BarcodeScanApi;

class BarcodeScanService
{
    private $apiKey;
    public function __construct(string $apiKey){
        $this->apiKey = $apiKey;
    }
    public function getCode(string $filepath){
        print_r("started barcode scan");
        $config=Configuration::getDefaultConfiguration()->setApiKey('APIkey',$this->apiKey);
        $apiInstance=new BarcodeScanAPI(new Client(), $config);
        try{
            $result=$apiInstance->barcodeScanImage($filepath);
            return $result;
        }catch (\Exception $exception){
            throw new \RuntimeException('Errror during the barcode scan: ' . $exception->getMessage());
        }
    }

}