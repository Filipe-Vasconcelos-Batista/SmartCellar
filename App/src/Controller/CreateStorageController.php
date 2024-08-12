<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CreateStorageController extends AbstractController
{
    #[Route('/create/storage', name: 'app_create_storage')]
    public function index(): Response
    {
        return $this->render('create_storage/index.html.twig', [
            'controller_name' => 'CreateStorageController',
        ]);
    }
}
