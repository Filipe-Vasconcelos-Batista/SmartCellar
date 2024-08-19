<?php

namespace App\Message;
use App\Entity\Products;
use App\Services\BarcodeScanService;
use App\Services\CacheService;
use App\Services\ProductLookupService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;



#[AsMessageHandler]
class PhotoInsertHandler
{
    private BarcodeScanService $barcodeScanService;
    private ProductLookupService $productLookUpService;
    private CacheService $cacheService;
    private EntityManagerInterface $entityManager;

    public function __construct(BarcodeScanService $barcodeScan, ProductLookupService $productLookupService, CacheService $cacheService, EntityManagerInterface $entityManager){
        $this->barcodeScanService=$barcodeScan;
        $this->productLookUpService=$productLookupService;
        $this->cacheService = $cacheService;
        $this->entityManager = $entityManager;
    }
    public function __invoke(PhotoInsertMessage $message )
    {
        echo "Handler invoked\n";
        error_log("Handler invoked");
        $filepath = $message->getFilepath();
        $barcode = $this->barcodeScanService->getCode($filepath);
        $cacheKey = $message->getId();
        if ($barcode) {
            $newProductInfo=$this->entityManager->getRepository(Products::class)->findOneBy(['barcode'=>$barcode]);
            if(!$newProductInfo){
                $newProductInfo = $this->productLookUpService->getProduct($barcode);
                if ($newProductInfo) {
                    $newProductInfo['barcode']=$barcode;
                    $this->cacheService->updateProductInfo($cacheKey, $newProductInfo);
                    return $cacheKey;
                }
            }
            else{
                $this->cacheService->updateProductInfo($cacheKey, (array)$newProductInfo);
                return $cacheKey;
            }
        }
        return null;
    }

}