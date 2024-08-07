<?php

namespace App\Controller;

use App\Form\InsertBarcodePhotoType;
use App\Message\UploadPhotoMessage;
use GuzzleHttp\Client;
use Swagger\Client\Api\BarcodeScanApi;
use Swagger\Client\Configuration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class InsertPhotoController extends AbstractController
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus){
        $this->messageBus=$messageBus;
    }

    #[Route('/insert/photo', name: 'app_insert_photo')]
    public function index(Request $request,SessionInterface $session): Response
    {
        $form=$this->createForm(InsertBarcodePhotoType::class);
        $form->handleRequest($request);
        $productInfo=null;
        if ($form->isSubmitted() && $form->isValid()) {
            $imageData = $form->get('photo')->getData();
            if ($imageData) {
                foreach ($imageData as $image) {
                    $filename = md5(uniqid()) . '.' . $image->guessExtension();
                    $filePath = $this->getParameter('photos_directory') . '/' . $filename;
                    $image->move($this->getParameter('photos_directory'), $filename);

                    $this->messageBus->dispatch(new UploadPhotoMessage($filePath));
                }
            }
        }

        if($session->has('productInfo')){
            $productInfo=$session->get('productInfo');
        }

        return $this->render('insert_photo/index.html.twig', [
            'form' => $form,
            'productInfo'=>$productInfo,
        ]);
    }

    public function upload(Request $request): JsonResponse{

        $files=$request->files->get('photo');
        var_dump($files);

        if($files){
            foreach ($files as $file) {
                print_r("it uploads");
                $filename=md5(uniqid()).'.'.$file->guessExtension();
                $filePath=$this->getParameter('photos_directory').'/'.$filename;
                $file->move($this->getParameter('photos_directory'),$filename);

                $this->messageBus->dispatch(new UploadPhotoMessage($filePath));

            }
            return new JsonResponse(['status'=>'success']);
        }
        return new JsonResponse(['status'=>'error']);
    }
}
