<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\Storage;
use App\Entity\StorageItems;
use App\Form\BarcodeType;
use App\Form\PhotoType;
use App\Form\ProductsType;
use App\Message\BarcodeInsertMessage;
use App\Message\PhotoInsertMessage;
use App\Repository\StorageItemsRepository;
use App\Services\CacheService;
use App\Services\PhotosService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class InsertController extends AbstractController
{
    private MessageBusInterface $messageBus;
    private CacheService $cache;
    private PhotosService $photosService;
    private StorageItemsRepository $storageItemsRepository;

    public function __construct(MessageBusInterface $messageBus, CacheService $cache, PhotosService $photosService, StorageItemsRepository $storageItemsRepository)
    {
        $this->messageBus = $messageBus;
        $this->cache = $cache;
        $this->photosService = $photosService;
        $this->storageItemsRepository = $storageItemsRepository;
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/insert/photo/{id}', name: 'app_insert_photo')]
    public function index(Request $request, SessionInterface $session, int $id): Response
    {
        $session->set('last_accessed_url', $this->generateUrl('app_insert_photo', ['id' => $id]));
        $form = $this->createForm(PhotoType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageData = $form->get('photo')->getData();
            $filePaths = $this->photosService->savePhotos($imageData);

            foreach ($filePaths as $filePath) {
                try {
                    $this->messageBus->dispatch(new PhotoInsertMessage($filePath, $id));
                    $this->addFlash('success', 'Photo submitted and processing started.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'An error occurred while trying to process the photo'.$e->getMessage());
                }
            }
        }
        $items = $this->cache->getCachedProductInfo('storage'.$id);

        return $this->render('insert/index.html.twig', [
            'form' => $form,
            'productInfo' => $items,
            'id' => ['id' => $id],
        ]);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/insert/barcode/{id}', name: 'app_insert_barcode')]
    public function insertBarcode(Request $request, SessionInterface $session, int $id): Response
    {
        $session->set('last_accessed_url', $this->generateUrl('app_insert_barcode', ['id' => $id]));
        $form = $this->createForm(BarcodeType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $barcode = (string) $form->get('barcode')->getData();
            try {
                $this->messageBus->dispatch(new BarcodeInsertMessage($barcode, $id));
                $this->addFlash('success', 'Barcode submitted and processing started.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while trying to process the barcode:'.$e->getMessage());
            }
        }
        $items = $this->cache->getCachedProductInfo('storage'.$id);

        return $this->render('insert/insertBarcode.html.twig', [
            'form' => $form,
            'productInfo' => $items,
            'id' => ['id' => $id],
        ]);
    }

    #[Route('/insert/final/{id}', name: 'app_finish')]
    public function finish(Request $request, int $id, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $items = $this->cache->getCachedProductInfo('storage'.$id);
        foreach ($items as $item) {
            $product = $entityManager->getRepository(Products::class)->findOneBy(['barcode' => $item['barcode']]);
            if ($product) {
                $item['id'] = $product->getId();
            }
            if (!isset($item['id'])) {
                $form = $this->createForm(ProductsType::class);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $this->setProductInDatabase($entityManager, $id, $form, $item);

                    return $this->redirectToRoute('app_finish', ['id' => $id]);
                }

                return $this->render('insert/form_insert.html.twig', [
                    'form' => $form,
                    'item' => $item,
                    'id' => ['id' => $id],
                ]);
            } else {
                $this->setProductInStorage($entityManager, $id, $item);
                $this->cache->deleteProductInfo($id, $item['barcode']);
            }
        }
        $lastAccessedUrl = $session->get('last_accessed_url');

        return $this->redirect($lastAccessedUrl);
    }

    private function setProductInDatabase(EntityManagerInterface $entityManager, int $id, object $form, array $item): void
    {
        $product = new Products();
        $product->setBarcode($item['barcode']);
        $product->setTitle($form->get('title')->getData());
        $product->setCategory($form->get('category')->getData());
        $entityManager->persist($product);
        $entityManager->flush();
        $item['title'] = $product->getTitle();
        $item['id'] = $product->getId();
        $item['category'] = $product->getCategory();
        $this->cache->updateProductInfo($id, $item);
    }

    /**
     * @param array[] $item
     */
    private function setProductInStorage(EntityManagerInterface $entityManager, int $id, array $item): void
    {
        $product = $this->storageItemsRepository->findStorageItemByProductIdAndStorageId($item['id'], $id);
        if (!$product) {
            $product = $entityManager->getRepository(Products::class)->find($item['id']);
            $storageId = $entityManager->getRepository(Storage::class)->find($id);
            $storageItem = new StorageItems();
            $storageItem->setStorageId($storageId);
            $storageItem->addProductId($product);
            $quantity = $storageItem->getQuantity();
            $adjust = (int) $item['quantity'] + $quantity;
            $storageItem->setQuantity($adjust);
            $entityManager->persist($storageItem);
        } else {
            $product->setQuantity($product->getQuantity() + $item['quantity']);
            $entityManager->persist($product);
        }
        $entityManager->flush();
    }
}
