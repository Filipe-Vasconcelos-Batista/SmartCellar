<?php

namespace App\Message;
use App\Services\BarcodeScanService;
use App\Services\ProductLookupService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;



#[AsMessageHandler]
class MessageHandler
{
    private $barcodeScanService;
    private $productLookUpService;
    private $cache;

    public function __construct(BarcodeScanService $barcodeScan, ProductLookupService $productLookupService, CacheInterface $cache){
        $this->barcodeScanService=$barcodeScan;
        $this->productLookUpService=$productLookupService;
        $this->cache = $cache;    }
    public function __invoke(UploadPhotoMessage $message, )
    {
        error_log("Handler invoked");
        $filepath = $message->getFilepath();

        $barcode = $this->barcodeScanService->getCode($filepath);
        if ($barcode) {
            $newProductInfo = $this->productLookUpService->getProduct($barcode);
            $cacheKey = "newProductInfo";
            $items=$this->cache->getItem($cacheKey);
            if($items->isHit()) {
               $existingProductInfo=$items->get();
            }else{
                $existingProductInfo=[];
            }
            $existingProductInfo[]=$newProductInfo;
            $items->set($existingProductInfo);
            $this->cache->save($items);


            return $cacheKey;

        }
        return null;
    }

}