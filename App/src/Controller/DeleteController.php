<?php

namespace App\Controller;

use App\Services\CacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class DeleteController extends AbstractController
{
    private CacheService $cacheService;
    public function __construct(CacheService $cacheService){
        $this->cacheService = $cacheService;
    }
    #[Route('/delete/entry/{id}/{barcode}', name: 'app_delete_entry')]
    public function deleteEntry(string $barcode,SessionInterface $session,string $id): Response
    {
        $this->cacheService->deleteProductInfo($id, $barcode);
        $lastAccessedUrl = $session->get('last_accessed_url');

        return $this->redirect($lastAccessedUrl);
    }
}
