<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Form\MembreType;
use App\Service\MailService;
use App\Form\RegistrationFormType;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $message = null;
        $user = new Membre();
        $form = $this->createForm(MembreType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
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

                $user->setPhoto($newFilename);
            }
            // encode the plain password
            $searchEmail = $entityManager->getRepository(Membre::class)->findOneByEmail($user->getEmail());

            if (!$searchEmail) {
                
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            } else {
                $message = "L'email que vous avez enregistré existe déjà";
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "Un email de bienvenue vous a été envoyé");
            $mail = new MailService();
            $content = " Hello " . $user->getPseudo() . "!" . ' ' . " Bienvenue sur notre site ! Clique sur le lien en bas pour revenir sur le site";
            $mail->send($user->getEmail(), $user->getPseudo(), 'Bienvenue', $content);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'message' => $message
        ]);
    }
    
    
    #[Route('/admin/membres', name: "admin_membres")]
    public function adminMembre(Request $globals, MembreRepository $repo, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $manager,Membre $membre = null)
    {

        $membres = $repo->findAll();
        

        if($membre ==null):
            $membre = new Membre;
        endif;


        $form= $this->createForm(RegistrationFormType::class, $membre);

        $form->handleRequest($globals);


        if($form->isSubmitted() && $form->isValid())
        {
            $membre->setPassword(
                $userPasswordHasher->hashPassword(
                    $membre,
                    $form->get('plainPassword')->getData()
                )
            );
            $manager->persist($membre);
            $manager->flush();
            
            $this->addFlash('success', "Utilisateur a bien été enregistré");

            return $this->redirectToRoute('admin_membres');
        }

        return $this->renderForm("admin/admin_membres.html.twig", [
            "formMembre" => $form,
            "editMode" => $membre->getId() !== null,
            'membres' => $membres,
        ]);

    }

    #[Route('/admin/membre/edit/{id}', name:'admin_edit_membre')]
    public function editMembre(Request $globals, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $manager, Membre $membre = null)
    {
        $form= $this->createForm(RegistrationFormType::class, $membre);

        $form->handleRequest($globals);


        if($form->isSubmitted() && $form->isValid())
        {
            $membre->setPassword(
                $userPasswordHasher->hashPassword(
                    $membre,
                    $form->get('plainPassword')->getData()
                )
            );
            $manager->persist($membre);
            $manager->flush();
            $this->addFlash('success', "Utilisateur a bien été enregistré");

            return $this->redirectToRoute('admin_membres');
        }

        return $this->renderForm("admin/admin_edit_membre.html.twig", [
            "registrationForm" => $form,
            "editMode" => $membre->getId() !== null
        ]);

    }
    #[Route("/admin/membre/delete/{id}", name:"admin_delete_membre")]

    public function deleteArticle(Membre $membre, EntityManagerInterface $manager)
    {
        $manager->remove($membre);
        $manager->flush();
        $this->addFlash('success', "L'utilisateur a bien été supprimé'");
        return $this->redirectToRoute('admin_membres');
    }
}
