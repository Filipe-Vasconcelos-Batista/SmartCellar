<?php

namespace App\MessageHandler;

use App\Entity\Products;
use App\Message\BarcodeInsertMessage;
use App\Services\ApiProductLookupService;
use App\Services\CacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class BarcodeInsertHandler
{
    private ApiProductLookupService $productLookupService;
    private CacheService $cacheService;
    private EntityManagerInterface $entityManager;

    public function __construct(ApiProductLookupService $productLookupService, CacheService $cacheService, EntityManagerInterface $entityManager)
    {
        $this->productLookupService = $productLookupService;
        $this->cacheService = $cacheService;
        $this->entityManager = $entityManager;
    }

    public function __invoke(BarcodeInsertMessage $barcodeLookupMessage): void
    {
        $barcode = $barcodeLookupMessage->getBarcode();
        $cacheKey = $barcodeLookupMessage->getId();
        $productInfo = $this->entityManager->getRepository(Products::class)->findOneBy(['barcode' => $barcode]);
        if (!$productInfo) {
            $productInfo = $this->productLookupService->getProduct($barcode);
            if ($productInfo) {
                $productInfo['barcode'] = $barcode;
                $productInfo['title'] = 'works';
                $productInfo['category'] = 'stuff';
                $this->cacheService->updateProductQuantity($cacheKey, $productInfo);
            }
        } else {
            $item = [];
            $item['id'] = $productInfo->getId();
            $item['barcode'] = $productInfo->getBarcode();
            $item['title'] = $productInfo->getTitle();
            $item['category'] = $productInfo->getCategory();
            $this->cacheService->updateProductQuantity($cacheKey, $item);
        }
    }
}
