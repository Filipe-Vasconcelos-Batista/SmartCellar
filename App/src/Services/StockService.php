<?php

namespace App\Services;

use App\Repository\StorageItemsRepository;
use Doctrine\ORM\EntityManagerInterface;

class StockService
{
    private StorageItemsRepository $storageItemsRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(StorageItemsRepository $storageItemsRepository, EntityManagerInterface $entityManager)
    {
        $this->storageItemsRepository = $storageItemsRepository;
        $this->entityManager = $entityManager;
    }

    public function reduceStock(string $barcode, int $storageId, int $number):void
    {
        $storageItem = $this->storageItemsRepository->findStorageItemByBarcodeAndStorageId($barcode, $storageId);
        if ($storageItem===null) {
            throw new \Exception("Nothing found for barcode $barcode to reduce");
        }
        $currentQuantity = $storageItem->getQuantity();
        $newQuantity = max(0, $currentQuantity - $number);
        $storageItem->setQuantity($newQuantity);

        $this->entityManager->persist($storageItem);
        $this->entityManager->flush();
    }

    public function addStock(string $barcode, int $storageId, int $number):void
    {
        $storageItem = $this->storageItemsRepository->findStorageItemByBarcodeAndStorageId($barcode, $storageId);
        if ($storageItem===null) {
            throw new \Exception("Nothing found for barcode $barcode to reduce");
        }
        $currentQuantity = $storageItem->getQuantity();
        $newQuantity = max(0, $currentQuantity + $number);
        $storageItem->setQuantity($newQuantity);

        $this->entityManager->persist($storageItem);
        $this->entityManager->flush();
    }
}
