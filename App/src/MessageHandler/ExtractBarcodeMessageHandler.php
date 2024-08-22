<?php

namespace App\MessageHandler;

use App\Message\BarcodeExtractMessage;
use App\Repository\StorageItemsRepository;
use App\Services\StockService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExtractBarcodeMessageHandler
{
    private EntityManagerInterface $entityManager;
    private StorageItemsRepository $storageItemsRepository;
    private StockService $stockService;

    public function __construct(StorageItemsRepository $storageItemsRepository, EntityManagerInterface $entityManager, StockService $stockService)
    {
        $this->entityManager = $entityManager;
        $this->storageItemsRepository = $storageItemsRepository;
        $this->stockService = $stockService;
    }

    public function __invoke(BarcodeExtractMessage $barcodeExtractMessage): void
    {
        $barcode = $barcodeExtractMessage->getBarcode();
        $storageId = $barcodeExtractMessage->getId();
        $this->stockService->reduceStock($barcode, $storageId,1);
    }
}
