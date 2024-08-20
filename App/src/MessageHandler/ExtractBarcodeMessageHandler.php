<?php

namespace App\MessageHandler;

use App\Entity\Products;
use App\Entity\StorageItems;
use App\Message\BarcodeExtractMessage;
use App\Message\BarcodeInsertMessage;
use App\Repository\StorageItemsRepository;
use App\Services\StockService;
use App\Services\CacheService;
use App\Services\ApiProductLookupService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ExtractBarcodeMessageHandler
{


    private EntityManagerInterface $entityManager;
    private StorageItemsRepository $storageItemsRepository;
    private StockService $backupService;
    public function __construct(StorageItemsRepository $storageItemsRepository,EntityManagerInterface $entityManager){

        $this->entityManager = $entityManager;
        $this->storageItemsRepository = $storageItemsRepository;
        $this->backupService = new StockService();
    }
    public function __invoke(BarcodeExtractMessage $barcodeExtractMessage):bool
    {
        $barcode = $barcodeExtractMessage->getBarcode();
        $storageId = $barcodeExtractMessage->getId();
        return $this->backupService->reduceStock($barcode, $storageId);
    }
}
