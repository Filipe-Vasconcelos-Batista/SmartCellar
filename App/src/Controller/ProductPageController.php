<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductPageController extends AbstractController
{
    #[Route('/product/page/{id}', name: 'app_product_page')]
    public function index(): Response
    {
        return $this->render('product_page/index.html.twig', [
            'controller_name' => 'ProductPageController',
        ]);
    }
}
