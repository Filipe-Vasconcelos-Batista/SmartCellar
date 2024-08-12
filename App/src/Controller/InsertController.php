<?php

namespace App\Controller;

use App\Form\InsertBarcodeType;
use App\Form\InsertPhotoType;
use App\Message\BarcodeLookupMessage;
use App\Message\UploadPhotoMessage;
use App\Services\CacheService;
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

    public function __construct(MessageBusInterface $messageBus, CacheService $cache){
        $this->messageBus=$messageBus;
        $this->cache = $cache;
    }

    #[Route('/insert/photo', name: 'app_insert_photo')]
    public function index(Request $request,SessionInterface $session): Response
    {
        $session->set('last_accessed_url', $this->generateUrl('app_insert_photo'));
        $form=$this->createForm(InsertPhotoType::class);
        $form->handleRequest($request);
        $items=null;
        if ($form->isSubmitted() && $form->isValid()) {
            $imageData = $form->get('photo')->getData();
            if ($imageData) {
                foreach ($imageData as $image) {
                    $filename = md5(uniqid()) . '.' . $image->guessExtension();
                    $filePath = $this->getParameter('photos_directory') . '/' . $filename;
                    $image->move($this->getParameter('photos_directory'), $filename);
                    $this->messageBus->dispatch(new UploadPhotoMessage($filePath));
                    $this->addFlash('success','Photo submitted and processing started.');
                }
            }
        }
        $cacheKey = "newProductInfo";
        $items = $this->cache->getCachedProductInfo($cacheKey);
        return $this->render('insert_photo/index.html.twig', [
            'form' => $form,
            'productInfo'=>$items,
        ]);
    }
    #[Route('/insert/barcode', name: 'app_insert_barcode')]
    public function insertBarcode(Request $request,SessionInterface $session): Response
    {
        $session->set('last_accessed_url', $this->generateUrl('app_insert_barcode'));
        $form=$this->createForm(InsertBarcodeType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $barcode=(string) $form->get('barcode')->getData();
            error_log("Barcode retrieved: " . $barcode);
            if (empty($barcode) || trim($barcode) === ''){
                $this->addFlash('error','Barcode is empty.');
            }
            else {
                $this->messageBus->dispatch(new BarcodeLookupMessage($barcode));
                $this->addFlash('success', 'Barcode submitted and processing started.');
            }
        }
        $items = $this->cache->getCachedProductInfo("newProductInfo");

        return $this->render('insert_photo/insertBarcode.html.twig', [
            'form' => $form,
            'productInfo'=>$items,
        ]);
    }
}
