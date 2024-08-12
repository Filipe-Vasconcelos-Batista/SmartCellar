<?php

namespace App\Controller;

use App\Entity\Storage;
use App\Entity\User;
use App\Form\StorageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StorageController extends AbstractController
{

    #[Route('/user/storages', name: 'app_user_storages')]
    public function index(Security $security): Response
    {
        $user=$security->getUser();
        if(!$user instanceof User){
            throw new\LogicException('The User is not of the expected type');
        }
        $storages=$user->getStorages();
        return $this->render('storage/storages.html.twig', [
            'storages' => $storages,
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
             $entityManager->persist($storage);
             $entityManager->flush();
             return $this->redirectToRoute('app_create_storage');
         }else{
             throw new \LogicException('The user is not of the expected type.');
         }
     }
        return $this->render('storage/index.html.twig', [
            'form' => $form,
        ]);
    }
}
