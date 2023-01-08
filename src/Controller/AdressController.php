<?php

namespace App\Controller;

use App\Entity\Adress;
use App\Form\AdressType;
use App\Service\CartService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdressController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {

        $this->entityManager = $entityManager;

    }
    #[Route('account/adress', name: 'app_adress')]
    public function index(): Response
    {
        return $this->render('account/adress.html.twig', [
            
        ]);
    }
    #[Route('account/adress/add/{id}', name: 'add_adress')]
    public function add(Request $request, CartService $cart): Response
    {
        $adress = new Adress();
        $form = $this->createForm(AdressType::class, $adress);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $adress->setMembre($this->getUser());
            $this->entityManager->persist($adress);
            $this->entityManager->flush();
            $this->addFlash('success', "L'adresse a bien été enregistré !");
            if ($cart->getCartWithData()) {
                return $this->redirectToRoute('app_order');
            } 
            else {
                return $this->redirectToRoute('app_adress');
            }
            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/add_adress.html.twig', [
            'formAdress' => $form->createView()
        ]);
    }

    #[Route('account/adress/edit/{id}', name: 'edit_adress')]
    public function edit(Request $request, $id): Response
    {
        $adress = $this->entityManager->getRepository(Adress::class)->findOneById($id);

        if (!$adress || $adress->getMembre() != $this->getUser()) {
            return $this->redirectToRoute('app_account');
        }

        $form = $this->createForm(AdressType::class, $adress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->flush();
            $this->addFlash('success', "L'adresse a bien été enregistré !");
            
            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/add_adress.html.twig', [
            'formAdress' => $form->createView()
        ]);
    }
    #[Route('account/adress/delete/{id}', name: 'delete_adress')]
    public function delete($id): Response
    {
        $adress = $this->entityManager->getRepository(Adress::class)->findOneById($id);

        // if ($adress && $adress->getMembre() == $this->getUser()) {
        //     return $this->redirectToRoute('app_account');
        // }
        $this->entityManager->remove($adress);
        $this->entityManager->flush();
        $this->addFlash('success', "L'adresse a bien été supprimé !");

        return $this->redirectToRoute('app_home');
    }
}
