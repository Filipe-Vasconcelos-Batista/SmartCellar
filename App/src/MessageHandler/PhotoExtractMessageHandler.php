<?php

namespace App\MessageHandler;

use App\Message\PhotoInsertMessage;
use App\Services\ApiBarcodeScanService;
use App\Services\PhotosService;
use App\Services\StockService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PhotoExtractMessageHandler
{
    private PhotosService  $photosService;
    private ApiBarcodeScanService $barcodeScanService;
    private StockService $stockService;



    public function __construct(ApiBarcodeScanService $barcodeScan, stockService $stockService, PhotosService $photosService ){
        $this->barcodeScanService=$barcodeScan;
        $this->stockService=$stockService;
        $this->photosService=$photosService;
    }
    public function __invoke(PhotoInsertMessage $message )
    {
        $storageId = $message->getId();
        $filepath = $message->getFilepath();
        $barcode = $this->barcodeScanService->getCode($filepath);
        if ($barcode) {
            $this->photosService->deletePhotos($filepath);
            return $this->stockService->reduceStock($barcode,$storageId, 1);
        }
        else return false;
    }
}
