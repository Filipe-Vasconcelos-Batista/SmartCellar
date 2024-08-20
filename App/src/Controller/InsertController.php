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
use App\Services\CacheService;
use App\Services\PhotosService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class InsertController extends AbstractController
{
    private MessageBusInterface $messageBus;
    private CacheService $cache;
    private PhotosService $photosService;


    public function __construct(MessageBusInterface $messageBus, CacheService $cache,PhotosService $photosService){
        $this->messageBus=$messageBus;
        $this->cache = $cache;
        $this->photosService = $photosService;
    }

    #[Route('/insert/photo/{id}', name: 'app_insert_photo')]
    public function index(Request $request,SessionInterface $session ,$id): Response
    {
        $session->set('last_accessed_url', $this->generateUrl('app_insert_photo',['id' => $id]));
        $form=$this->createForm(PhotoType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filePaths=$this->photosService->savePhotos($form);
            foreach ($filePaths as $filePath) {
                $this->messageBus->dispatch(new PhotoInsertMessage($filePath,$id));
            }
                    $this->addFlash('success','Photo submitted and processing started.');
                }
        $items = $this->cache->getCachedProductInfo("storage" . $id);
        return $this->render('insert/index.html.twig', [
            'form' => $form,
            'productInfo'=>$items,
            'id'=>['id'=>$id],
        ]);
    }
    #[Route('/insert/barcode/{id}', name: 'app_insert_barcode')]
    public function insertBarcode(Request $request,SessionInterface $session, $id): Response
    {
        $session->set('last_accessed_url', $this->generateUrl('app_insert_barcode',['id' => $id]));
        $form=$this->createForm(BarcodeType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $barcode=(string) $form->get('barcode')->getData();
            $this->messageBus->dispatch(new BarcodeInsertMessage($barcode,$id));
            $this->addFlash('success', 'Barcode submitted and processing started.');
            }
        $items = $this->cache->getCachedProductInfo("storage" . $id);
        return $this->render('insert/insertBarcode.html.twig', [
            'form' => $form,
            'productInfo'=>$items,
            'id'=>['id'=>$id],
        ]);
    }
    #[Route('/insert/final/{id}', name: 'app_finish')]
    public function finish(Request $request, $id,EntityManagerInterface $entityManager, SessionInterface $session): Response{
        $items = $this->cache->getCachedProductInfo("storage" . $id);
        foreach ($items as $item) {
            if(!isset($item['id'])){
                $form=$this->createForm(ProductsType::class);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $this->setProductInDatabase($entityManager,$id,$form,$item);
                    return $this->redirectToRoute('app_finish');
                }
                return $this->render('insert/form_insert.html.twig', [
                    'form' => $form,
                    'item'=>$item,
                ]);
            }
            else{
                $this->setProductInStorage($entityManager,$id,$item);
            }
        }
        $lastAccessedUrl = $session->get('last_accessed_url');

        return $this->redirect($lastAccessedUrl);
    }

    private function setProductInDatabase(EntityManagerInterface $entityManager,int $id,$form, Array $item):void{
        $product=new Products();
        $product->setBarcode($item['barcode']);
        $product->setTitle($form->get('title')->getData());
        $product->setCategory($form->get('category')->getData());
        $entityManager->persist($product);
        $entityManager->flush();
        $item['title']=$product->getTitle();
        $item['id']=$product->getId();
        $item['category']=$product->getCategory();
        $this->cache->saveProductInfo('storage' . $id, $item);
    }
    private function setProductInStorage(EntityManagerInterface $entityManager,int $id, Array $item):void{
        $product=$entityManager->getRepository(Products::class)->find($item['id']);
        if($product){
            $storageId= $entityManager->getRepository(Storage::class)->find($id);
            $storageItem=new StorageItems();
            $storageItem->setStorageId($storageId);
            $storageItem->addProductId($product);
            $quantity=$storageItem->getQuantity();
            $storageItem->setQuantity($item['quantity'] + $quantity);
            $entityManager->persist($storageItem);
            $entityManager->flush();
        }
    }
}
