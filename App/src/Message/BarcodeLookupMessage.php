<?php

namespace App\Message;

class BarcodeLookupMessage
{
    private string $barcode;
    public function __construct(string $barcode){
        $this->barcode=$barcode;
    }
    public function getBarcode(): string{
        return $this->barcode;
    }
}