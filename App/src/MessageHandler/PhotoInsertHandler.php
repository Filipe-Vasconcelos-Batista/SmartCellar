<?php

namespace App\MessageHandler;
use App\Entity\Products;
use App\Message\PhotoInsertMessage;
use App\Services\ApiBarcodeScanService;
use App\Services\CacheService;
use App\Services\ApiProductLookupService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;


#[AsMessageHandler]
class PhotoInsertHandler
{
    private ApiBarcodeScanService $barcodeScanService;
    private ApiProductLookupService $productLookUpService;
    private CacheService $cacheService;
    private EntityManagerInterface $entityManager;

    public function __construct(ApiBarcodeScanService $barcodeScan, ApiProductLookupService $productLookupService, CacheService $cacheService, EntityManagerInterface $entityManager){
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