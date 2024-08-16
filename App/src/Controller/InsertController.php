<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\Storage;
use App\Entity\StorageItems;
use App\Form\InsertBarcodeType;
use App\Form\InsertPhotoType;
use App\Form\ProductsType;
use App\Message\BarcodeLookupMessage;
use App\Message\UploadPhotoMessage;
use App\Services\CacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class InsertController extends AbstractController
{
    private MessageBusInterface $messageBus;
    private CacheService $cache;


    public function __construct(MessageBusInterface $messageBus, CacheService $cache, Security $security){
        $this->messageBus=$messageBus;
        $this->cache = $cache;
    }

    #[Route('/insert/photo/{id}', name: 'app_insert_photo')]
    public function index(Request $request,SessionInterface $session ,$id): Response
    {
        $session->set('last_accessed_url', $this->generateUrl('app_insert_photo',['id' => $id]));
        $form=$this->createForm(InsertPhotoType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageData = $form->get('photo')->getData();
            if ($imageData) {
                foreach ($imageData as $image) {
                    $filename = md5(uniqid()) . '.' . $image->guessExtension();
                    $filePath = $this->getParameter('photos_directory') . '/' . $filename;
                    $image->move($this->getParameter('photos_directory'), $filename);
                    $this->messageBus->dispatch(new UploadPhotoMessage($filePath,$id));
                    $this->addFlash('success','Photo submitted and processing started.');
                }
            }
        }
        $items = $this->cache->getCachedProductInfo("storage" . $id);
        return $this->render('insert/index.html.twig', [
            'form' => $form,
            'productInfo'=>$items,
            'id'=>$id,
        ]);
    }
    #[Route('/insert/barcode/{id}', name: 'app_insert_barcode')]
    public function insertBarcode(Request $request,SessionInterface $session, $id): Response
    {
        $session->set('last_accessed_url', $this->generateUrl('app_insert_barcode',['id' => $id]));
        $form=$this->createForm(InsertBarcodeType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $barcode=(string) $form->get('barcode')->getData();
            if (empty($barcode) || trim($barcode) === ''){
                $this->addFlash('error','Barcode is empty.');
            }
            else {
                $this->messageBus->dispatch(new BarcodeLookupMessage($barcode,$id));
                $this->addFlash('success', 'Barcode submitted and processing started.');
            }
        }
        $items = $this->cache->getCachedProductInfo("storage" . $id);
        return $this->render('insert/insertBarcode.html.twig', [
            'form' => $form,
            'productInfo'=>$items,
            'id'=>['id'=>$id],
        ]);
    }
    #[Route('/insert/final/{id}', name: 'app_finish')]
    public function finish(Request $request, $id,EntityManagerInterface $entityManager, SessionInterface $session): Response
    {

        $items = $this->cache->getCachedProductInfo("storage" . $id);
        foreach ($items as $item) {
            if(!isset($item['id'])){
                $product=new Products();
                $form=$this->createForm(ProductsType::class);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
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
                return $this->render('insert/form_insert.html.twig', [
                    'form' => $form,
                    'item'=>$item,
                ]);
            }
            else{

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
        $lastAccessedUrl = $session->get('last_accessed_url');

        return $this->redirect($lastAccessedUrl);
    }
}
