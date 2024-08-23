<?php

namespace App\MessageHandler;

use App\Message\BarcodeExtractMessage;
use App\Services\StockService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

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
        try {
            $this->stockService->reduceStock($barcode, $storageId, 1);
        } catch (\Exception $exception) {
            $envelope = new Envelope($barcodeExtractMessage);
            throw new HandlerFailedException($envelope, [$exception]);
        }
    }

}
