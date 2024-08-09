<?php

namespace App\Message;
use App\Services\BarcodeScanService;
use App\Services\CacheService;
use App\Services\ProductLookupService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;



#[AsMessageHandler]
class MessageHandler
{
    private $barcodeScanService;
    private $productLookUpService;
    private $cacheService;

    public function __construct(BarcodeScanService $barcodeScan, ProductLookupService $productLookupService, CacheService $cacheService){
        $this->barcodeScanService=$barcodeScan;
        $this->productLookUpService=$productLookupService;
        $this->cacheService = $cacheService;    }
    public function __invoke(UploadPhotoMessage $message )
    {
        error_log("Handler invoked");
        $filepath = $message->getFilepath();
        $barcode = $this->barcodeScanService->getCode($filepath);
        $cacheKey = 'newProductInfo';
        if ($barcode) {
            $newProductInfo = $this->productLookUpService->getProduct($barcode);
            if ($newProductInfo) {
                $newProductInfo['barcode']=$barcode;
                print_r($newProductInfo);
                $this->cacheService->updateProductInfo($cacheKey, $newProductInfo);
                return $cacheKey;
            }
        }
        return null;
    }

}