<?php

namespace App\Services;

use App\Repository\StorageItemsRepository;
use Doctrine\ORM\EntityManagerInterface;

class StockService

{
    private StorageItemsRepository $storageItemsRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(StorageItemsRepository $storageItemsRepository, EntityManagerInterface $entityManager){
    $this->storageItemsRepository=$storageItemsRepository;
    $this->entityManager=$entityManager;

}
    public function reduceStock(string $barcode, int $storageId, int $number=1)
    {
        $storageItem = $this->storageItemsRepository->findStorageItemByBarcodeAndStorageId($barcode, $storageId);
        if (!$storageItem) {
            return false;
        }
        $currentQuantity = $storageItem->getQuantity();
        $newQuantity = max(0, $currentQuantity - $number);
        $storageItem->setQuantity($newQuantity);

        $this->entityManager->persist($storageItem);
        $this->entityManager->flush();

        return true;
    }
    public function addStock(string $barcode, int $storageId, int $number)
    {
        $storageItem = $this->storageItemsRepository->findStorageItemByBarcodeAndStorageId($barcode, $storageId);
        if (!$storageItem) {
            return false;
        }
        $currentQuantity = $storageItem->getQuantity();
        $newQuantity = max(0, $currentQuantity + $number);
        $storageItem->setQuantity($newQuantity);

        $this->entityManager->persist($storageItem);
        $this->entityManager->flush();

        return true;
    }

}