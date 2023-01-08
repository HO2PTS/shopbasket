<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Membre;
use App\Form\MembreType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig');
    }
    #[Route('/account/order', name: 'account_order')]
    public function order(EntityManagerInterface $manager)
    {
        $orders = $manager->getRepository(Order::class)->findSuccessOrders($this->getUser());
        
        return $this->render('account/account_order.html.twig', [
            'orders' => $orders
        ]);
    }
    #[Route('/account/order/{reference}', name: 'show_order')]
    public function showOrder(EntityManagerInterface $manager,$reference)
    {
        $order = $manager->getRepository(Order::class)->findOneByReference($reference);
        
        if(!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('account_order');
        }
        return $this->render('account/account_order_show.html.twig', [
            'order' => $order
        ]);
    }
    #[Route('/account/membre/edit/{id}', name:'edit_membre')]
    public function editMembre(Request $globals, UserPasswordHasherInterface $userPasswordHasher, SluggerInterface $slugger, EntityManagerInterface $manager, Membre $membre = null)
    {
        $form= $this->createForm(MembreType::class, $membre);

        $form->handleRequest($globals);


        if($form->isSubmitted() && $form->isValid())
        {
            $membre->setPassword(
                $userPasswordHasher->hashPassword(
                    $membre,
                    $form->get('plainPassword')->getData()
                )
            );
            $photo = $form->get('photo')->getData();

            if ($photo) {
                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'.'.$photo->guessExtension();
                try {
                    $photo->move(
                        $this->getParameter('image_membre'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $membre->setPhoto($newFilename);
            }

            $manager->persist($membre);
            $manager->flush();
            $this->addFlash('success', "Utilisateur a bien été enregistré");

            return $this->redirectToRoute('app_account');
        }

        return $this->renderForm("account/edit_membre.html.twig", [
            "registrationForm" => $form,
            "editMode" => $membre->getId() !== null
        ]);

    }
}
