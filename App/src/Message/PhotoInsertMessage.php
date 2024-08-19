<?php

namespace App\Message;

class PhotoInsertMessage
{
    private string $filePath;
    private int $id;
    public function __construct(string $filePath, int $id){
        $this->filePath = $filePath;
        $this->id = $id;
    }
    public function getFilePath(): string{
        return $this->filePath;
    }
    public function getId(): int{
        return $this->id;
    }

}

