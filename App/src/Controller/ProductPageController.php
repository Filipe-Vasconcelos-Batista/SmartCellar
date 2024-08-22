<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\StorageItemType;
use App\Repository\StorageItemsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductPageController extends AbstractController
{
    private StorageItemsRepository $storageItems;

    public function __construct(StorageItemsRepository $storageItems)
    {
        $this->storageItems = $storageItems;
    }

    #[Route('/product/{productId}/{storageId}', name: 'app_product_page')]
    public function index(EntityManagerInterface $entityManager, Request $request, $storageId, $productId): Response
    {/*
        $product=$entityManager->getRepository(Products::class)->find($productId);

        $item=$this->storageItems->findStorageItemByProductIdAndStorageId($productId, $storageId);
        $itemInfo['minQuantity']=$item->getQuantity();
        $itemInfo['quantity']=$item->getMinQuantity();
        $form=$this->createForm(StorageItemType::class);
        if ($form->isSubmitted() && $form->isValid()) {
            $item->setQuantity($form->get('quantity')->getData());
            $item->setMinQuantity($form->get('minQuantity')->getData());
            $entityManager->persist($item);
            $entityManager->flush();
        }*/
        return $this->render('product_page/index.html.twig', [
        ]);
    }

}
