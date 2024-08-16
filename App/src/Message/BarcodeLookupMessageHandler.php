<?php

namespace App\Message;


use App\Entity\Products;
use App\Services\CacheService;
use App\Services\ProductLookupService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class BarcodeLookupMessageHandler
{
    private ProductLookupService $productLookupService;
    private CacheService $cacheService;
    private EntityManagerInterface $entityManager;

    public function __construct(ProductLookupService $productLookupService, CacheService $cacheService,EntityManagerInterface $entityManager){
        $this->productLookupService = $productLookupService;
        $this->cacheService = $cacheService;
        $this->entityManager = $entityManager;
    }
    public function __invoke(BarcodeLookupMessage $barcodeLookupMessage){
        $barcode =$barcodeLookupMessage->getBarcode();
        $cacheKey = $barcodeLookupMessage->getId();
        $productInfo=$this->entityManager->getRepository(Products::class)->findOneBy(['barcode'=>$barcode]);
        if(!$productInfo) {
            $productInfo = $this->productLookupService->getProduct($barcode);
            if ($productInfo) {
                $productInfo['barcode'] = $barcode;
                $this->cacheService->updateProductInfo($cacheKey, $productInfo);
            }
        }else{
            $item=[];
            $item['id']=$productInfo->getId();
            $item['barcode']=$productInfo->getBarcode();
            $item['title']=$productInfo->getTitle();
            $item['category']=$productInfo->getCategory();
            $this->cacheService->updateProductInfo($cacheKey,$item);
        }
    }
}