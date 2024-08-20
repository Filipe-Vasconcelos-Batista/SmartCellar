<?php

namespace App\MessageHandler;

use App\Message\PhotoInsertMessage;
use App\Services\ApiBarcodeScanService;
use App\Services\StockService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PhotoExtractMessageHandler
{
    private ApiBarcodeScanService $barcodeScanService;
    private StockService $stockService;



    public function __construct(ApiBarcodeScanService $barcodeScan, stockService $stockService ){
        $this->barcodeScanService=$barcodeScan;
        $this->stockService=$stockService;

    }
    public function __invoke(PhotoInsertMessage $message )
    {
        $storageId = $message->getId();
        $filepath = $message->getFilepath();
        $barcode = $this->barcodeScanService->getCode($filepath);
        if ($barcode) {
            return $this->stockService->reduceStock($barcode,$storageId, 1);
        }
        else return false;
    }
}
