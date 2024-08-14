<?php

namespace App\Message;


use App\Services\CacheService;
use App\Services\ProductLookupService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class BarcodeLookupMessageHandler
{
    private ProductLookupService $productLookupService;
    private CacheService $cacheService;

    public function __construct(ProductLookupService $productLookupService, CacheService $cacheService){
        $this->productLookupService = $productLookupService;
        $this->cacheService = $cacheService;
    }
    public function __invoke(BarcodeLookupMessage $barcodeLookupMessage){
        error_log("Handler invoked");
        $barcode =$barcodeLookupMessage->getBarcode();
        $productInfo=$this->productLookupService->getProduct($barcode);
        $cacheKey = $barcodeLookupMessage->getId();
        if($productInfo){
            $productInfo['barcode']=$barcode;
            $this->cacheService->updateProductInfo($cacheKey,$productInfo);
        }
    }
}