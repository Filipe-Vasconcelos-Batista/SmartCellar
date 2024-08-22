<?php

namespace App\MessageHandler;

use App\Message\BarcodeExtractMessage;
use App\Services\StockService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class BarcodeExtractMessageHandler
{
    private StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function __invoke(BarcodeExtractMessage $barcodeExtractMessage): void
    {
        $barcode = $barcodeExtractMessage->getBarcode();
        $storageId = $barcodeExtractMessage->getId();
        $this->stockService->reduceStock($barcode, $storageId, 1);
    }
}
