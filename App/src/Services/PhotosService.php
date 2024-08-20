<?php

namespace App\Services;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PhotosService extends AbstractController
{
    public function savePhotos($form):array
    {
        $filePaths=[];
        $imageData = $form->get('photo')->getData();
        foreach ($imageData as $image) {
                $filename = md5(uniqid()) . '.' . $image->guessExtension();
                $filePath = $this->getParameter('photos_directory') . '/' . $filename;
                $image->move($this->getParameter('photos_directory'), $filename);
                $filePaths[] = $filePath;
        }
        return $filePaths;
    }
    public function deletePhotos($filePath): void
    {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
}