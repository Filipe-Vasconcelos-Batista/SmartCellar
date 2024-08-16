<?php

namespace App\Controller;

use App\Entity\Storage;
use App\Entity\User;
use App\Form\StorageType;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StorageController extends AbstractController
{

    #[Route('/user/storage/{id?}', name: 'app_user_storages')]
    public function index(Security $security, $id=null, EntityManagerInterface $entityManager ): Response
    {
        $user=$security->getUser();
        $storages=$user->getStorages();
        $storage=null;
        $products=null;
        if($id){
            $storage = $entityManager->getRepository(Storage::class)->find($id);
            if(!$storage){
                throw $this->createNotFoundException('Storage not found');
            }
            if (!$storage->getUserId()->contains($user)) {
                return $this->redirectToRoute('app_user_storages');
            }
            $products=$storage->getStorageItems();
        }
            return $this->render('storage/storages.html.twig', [
                'storages' => $storages,
                'storage' => $storage,
                'products' => $products,
            ]);
    }
    #[Route('/storage/create', name: 'app_storage_create')]
    public function createStorage(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
     $storage=new Storage();
     $form=$this->createForm(StorageType::class, $storage);
     $form->handleRequest($request);
     if ($form->isSubmitted() && $form->isValid()) {
         $user=$security->getUser();
         if ($user instanceof User) {
             $storage->addUserId($user);
             $storage->setName($form->get('name')->getData());
             $entityManager->persist($storage);
             $entityManager->flush();
             return $this->redirectToRoute('app_user_storages');
         }else{
             throw new \LogicException('The user is not of the expected type.');
         }
     }
        return $this->render('storage/index.html.twig', [
            'form' => $form,
        ]);
    }
}
