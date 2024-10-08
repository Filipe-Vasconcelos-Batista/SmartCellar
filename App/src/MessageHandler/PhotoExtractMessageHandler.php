<?php

namespace App\MessageHandler;

use App\Message\PhotoExtractMessage;
use App\Services\ApiBarcodeScanService;
use App\Services\PhotosService;
use App\Services\StockService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PhotoExtractMessageHandler
{
    private PhotosService $photosService;
    private ApiBarcodeScanService $barcodeScanService;
    private StockService $stockService;

    public function __construct(ApiBarcodeScanService $barcodeScan, StockService $stockService, PhotosService $photosService)
    {
        $this->barcodeScanService = $barcodeScan;
        $this->stockService = $stockService;
        $this->photosService = $photosService;
    }

    public function __invoke(PhotoExtractMessage $message): void
    {
        $storageId = $message->getId();
        $filePath = $message->getFilepath();
        $barcode = $this->barcodeScanService->getCode($filePath);
        $this->photosService->deletePhotos($filePath);
        $this->stockService->reduceStock($barcode, $storageId, 1);
    }
}
