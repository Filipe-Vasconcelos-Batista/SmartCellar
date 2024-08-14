<?php

namespace App\Message;

class BarcodeLookupMessage
{
    private string $barcode;
    private int $id;
    public function __construct(string $barcode,int $id){
        $this->barcode=$barcode;
        $this->id=$id;
    }
    public function getBarcode(): string{
        return $this->barcode;
    }
    public function getId(): int{
        return $this->id;
    }
}