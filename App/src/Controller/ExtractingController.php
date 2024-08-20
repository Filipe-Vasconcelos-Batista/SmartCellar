<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\Storage;
use App\Entity\StorageItems;
use App\Form\BarcodeType;
use App\Form\PhotoType;
use App\Form\ProductsType;
use App\Message\BarcodeExtractMessage;
use App\Message\BarcodeInsertMessage;
use App\Message\PhotoInsertMessage;
use App\Services\CacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class ExtractingController extends AbstractController
{
    private MessageBusInterface $messageBus;
    private CacheService $cache;


    public function __construct(MessageBusInterface $messageBus, CacheService $cache, Security $security){
        $this->messageBus=$messageBus;
        $this->cache = $cache;
    }

    #[Route('/extract/photo/{id}', name: 'app_extract_photo')]
    public function index(Request $request,SessionInterface $session ,$id): Response
    {
        $session->set('last_accessed_url', $this->generateUrl('app_extract_photo',['id' => $id]));
        $form=$this->createForm(PhotoType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageData = $form->get('photo')->getData();
            if ($imageData) {
                foreach ($imageData as $image) {
                    $filename = md5(uniqid()) . '.' . $image->guessExtension();
                    $filePath = $this->getParameter('photos_directory') . '/' . $filename;
                    $image->move($this->getParameter('photos_directory'), $filename);
                    $this->messageBus->dispatch(new PhotoInsertMessage($filePath,$id));
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
    #[Route('/extract/barcode/{id}', name: 'app_extract_barcode')]
    public function insertBarcode(Request $request,SessionInterface $session, $id): Response
    {
        $session->set('last_accessed_url', $this->generateUrl('app_extract_barcode',['id' => $id]));
        $form=$this->createForm(BarcodeType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $barcode=(string) $form->get('barcode')->getData();
            $result=$this->messageBus->dispatch(new BarcodeExtractMessage($barcode,$id));
            if($result===false){
                $this->addFlash('error','Barcode not found inside your storage.');
            }
            else{
                $this->addFlash('success','Barcode submitted and processing started.');
            }
        }
        return $this->render('extracting/index.html.twig', [
            'form' => $form,
            'id'=>['id'=>$id],
        ]);
    }
}