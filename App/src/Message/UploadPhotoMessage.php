<?php

namespace App\Message;

class UploadPhotoMessage
{
    private $filePath;
    public function __construct(string $filePath){
        $this->filePath = $filePath;
    }
    public function getFilePath(): string{
        return $this->filePath;
    }
}

