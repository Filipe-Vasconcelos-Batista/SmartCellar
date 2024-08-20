<?php

namespace App\MessageHandler;
use App\Entity\Products;
use App\Message\PhotoInsertMessage;
use App\Services\ApiBarcodeScanService;
use App\Services\CacheService;
use App\Services\ApiProductLookupService;
use App\Services\PhotosService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;


#[AsMessageHandler]
class PhotoInsertHandler
{
    private ApiBarcodeScanService $barcodeScanService;
    private ApiProductLookupService $productLookUpService;
    private CacheService $cacheService;
    private EntityManagerInterface $entityManager;
    private PhotosService  $photosService;

    public function __construct(ApiBarcodeScanService $barcodeScan, ApiProductLookupService $productLookupService, CacheService $cacheService, EntityManagerInterface $entityManager, PhotosService $photosService){
        $this->barcodeScanService=$barcodeScan;
        $this->productLookUpService=$productLookupService;
        $this->cacheService = $cacheService;
        $this->entityManager = $entityManager;
        $this->photosService = $photosService;
    }
    public function __invoke(PhotoInsertMessage $message )
    {
        echo "Handler invoked\n";
        $filepath = $message->getFilepath();
        $barcode = $this->barcodeScanService->getCode($filepath);
        $cacheKey = $message->getId();
        if ($barcode) {
            $newProductInfo=$this->entityManager->getRepository(Products::class)->findOneBy(['barcode'=>$barcode[0]]);
            if(!$newProductInfo){
                echo 'went here';
                $newProductInfo = $this->productLookUpService->getProduct($barcode);
                if ($newProductInfo) {
                    echo 'whent also here';
                    $newProductInfo['barcode']=$barcode;
                    $this->cacheService->updateProductInfo($cacheKey, $newProductInfo);
                    return $cacheKey;
                }
            }
            else{
                $item=[];
                $item['id']=$newProductInfo->getId();
                $item['barcode']=$newProductInfo->getBarcode();
                $item['title']=$newProductInfo->getTitle();
                $item['category']=$newProductInfo->getCategory();
                $this->cacheService->updateProductInfo($cacheKey,$item);
                return $cacheKey;
            }
        }
        return null;
    }
}