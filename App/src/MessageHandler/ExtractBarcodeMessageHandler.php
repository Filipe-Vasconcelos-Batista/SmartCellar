<?php

namespace App\MessageHandler;

use App\Entity\Products;
use App\Entity\StorageItems;
use App\Message\BarcodeExtractMessage;
use App\Message\BarcodeInsertMessage;
use App\Repository\StorageItemsRepository;
use App\Services\CacheService;
use App\Services\ProductLookupService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ExtractBarcodeMessageHandler
{


    private EntityManagerInterface $entityManager;
    private StorageItemsRepository $storageItemsRepository;

    public function __construct(StorageItemsRepository $storageItemsRepository,EntityManagerInterface $entityManager){

        $this->entityManager = $entityManager;
        $this->storageItemsRepository = $storageItemsRepository;
    }
    public function __invoke(BarcodeExtractMessage $barcodeExtractMessage):bool{
        $barcode =$barcodeExtractMessage->getBarcode();
        $storageId=$barcodeExtractMessage->getId();
        $storageItem = $this->storageItemsRepository->findStorageItemByBarcodeAndStorageId($barcode,$storageId);
        if(!$storageItem) {
            return false;
        }
        $currentQuantity=$storageItem->getQuantity();
        $newQuantity=max(0,$currentQuantity-1);
        $storageItem->setQuantity($newQuantity);

        $this->entityManager->persist($storageItem);
        $this->entityManager->flush();

        return true;
        }
}
