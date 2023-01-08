<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Membre;
use App\Entity\Article;
use App\Entity\Carrier;
use App\Entity\Produit;
use App\Entity\Category;
use App\Entity\Commande;
use App\Form\ArticleType;
use App\Form\CarrierType;
use App\Form\ProduitType;
use App\Form\CategoryType;
use App\Entity\OrderDetails;
use App\Repository\OrderRepository;
use App\Repository\ArticleRepository;
use App\Repository\CarrierRepository;
use App\Repository\ProduitRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderDetailsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminController extends AbstractController
{
    // #[Route('/admin', name: 'app_admin')]
    // public function index(): Response
    // {
    //     return $this->render('admin/index.html.twig', [
    //         'controller_name' => 'AdminController',
    //     ]);
    // }
    
    /** CRUD PRODUIT */
    #[Route('/admin/produits', name: "admin_produits")]
    public function adminProduit( ProduitRepository $repo)
    {
   
        $produits = $repo->findAll();
        

        return $this->renderForm("admin/admin_produits.html.twig", [
            'produits' => $produits
        ]);

    }
    #[Route('/admin/produits/create', name: "admin_create_produit")]
    public function createProduit(Request $globals, EntityManagerInterface $manager,SluggerInterface $slugger, Produit $produit = null)
    {
        if($produit == null):
            $produit = new Produit;
        endif;


        $form = $this->createForm(ProduitType::class, $produit);

        $form->handleRequest($globals);



        if($form->isSubmitted() && $form->isValid())
        {
            $photo = $form->get('photo')->getData();
            if ($photo) {
                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'.'.$photo->guessExtension();
                try {
                    $photo->move(
                        $this->getParameter('image_produit'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $produit->setPhoto($newFilename);
            }
            $manager->persist($produit);
            $manager->flush();
            $this->addFlash('success', "Le produit a bien été enregistré !");
            
            return $this->redirectToRoute('admin_produits');
        }
        return $this->renderForm("admin/admin_create_produit.html.twig", [
            "formProduits" => $form,
        ]);
    }
    #[Route('/admin/produit/edit/{id}', name:'admin_edit_produit')]
    public function showEdit(Request $globals, EntityManagerInterface $manager, Produit $produit = null)
    {
        
        $form = $this->createForm(ProduitType::class, $produit);

        $form->handleRequest($globals);



        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($produit);
            $manager->flush();
            $this->addFlash('success', "Le produit a bien été enregistré !");
            
            return $this->redirectToRoute('admin_produits');
        }
        return $this->renderForm("admin/admin_edit_produit.html.twig", [ 
            "formProduits" => $form,
            "editMode" => $produit->getId() !== null
        ]);
    }
    #[Route('/admin/produit/delete/{id}', name:'admin_delete_produit')]

    public function deleteProduit(Produit $produit, EntityManagerInterface $manager)
    {

        $manager->remove($produit);
        $manager->flush();
        $this->addFlash('success', "Le produit a bien été supprimé");
        return $this->redirectToRoute('admin_produits');
    }
    #[Route('/admin/produit/show/{id}', name: 'admin_show_vehicule')]
    public function showProduit($id, ProduitRepository $repo, Request $globals, EntityManagerInterface $manager)  //$id correspond au {id} (paramètres de route) dans l'URL 
    {
        $produits = $repo->find($id);

        return $this->renderForm('admin/admin_show_vehicule.html.twig', [
            'produits' => $produits
        ]);

    }

    /** CRUD COMMANDE */
    #[Route('/admin/commande/edit/{id}', name:'admin_edit_commande')]
    #[Route('/admin/commande', name: 'admin_commandes')]
    public function adminCommandes(OrderDetailsRepository $drepo,OrderRepository $repo, Order $order = null)
    {
        $details = $drepo->findAll();
        $orders = $repo->findOrders();
        if($order == null) {

            $order = new Order;

        }

        return $this->renderForm("admin/admin_commandes.html.twig", [

            'details' => $details,
            'orders' => $orders
        ]);
    }
    #[Route('/admin/commande/{reference}', name: 'show_order')]
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
    #[Route('/admin/commande/detail/delete/{id}', name:'admin_delete_details')]
    public function deleteOrderDetails(OrderDetails $orderDetails, EntityManagerInterface $manager)
    {
        $manager->remove($orderDetails);
        $manager->flush();
        $this->addFlash('success', "La commande a bien été supprimé");
        return $this->redirectToRoute('admin_commandes');
    }

    /** CRUD ARTICLES */
    #[Route('/admin/articles', name: "admin_articles")]
    public function adminArticle(ArticleRepository $repo, )
    {
        $articles = $repo->findAll();
        

        return $this->renderForm("admin/admin_articles.html.twig", [
            'articles' => $articles
        ]);

    }
    #[Route('/admin/articles/create', name: "admin_create_article")]
    public function createArticle(Request $globals, EntityManagerInterface $manager, SluggerInterface $slugger, Article $article = null)
    {
        if($article == null):
            $article = new Article;
        endif;


        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($globals);



        if($form->isSubmitted() && $form->isValid())
        {
            $photo = $form->get('photo')->getData();
            if ($photo) {
                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'.'.$photo->guessExtension();
                try {
                    $photo->move(
                        $this->getParameter('image_article'), // Récupère le Path dans le config/service.yaml du dossier où je dois upload mon image
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $article->setPhoto($newFilename);
            }
            $article->setDateEnregistrement(new \DateTime);
            $manager->persist($article);
            $manager->flush();
            $this->addFlash('success', "L'article a bien été enregistré !");
            
            return $this->redirectToRoute('admin_articles');
        }
        return $this->renderForm("admin/admin_create_article.html.twig", [
            "formArticles" => $form,
        ]);
    }
    #[Route('/admin/article/edit/{id}', name:'admin_edit_article')]
    public function showEditArticle (Request $globals, EntityManagerInterface $manager, Article $article = null)
    {
        
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($globals);



        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($article);
            $manager->flush();
            $this->addFlash('success', "L'article a bien été enregistré !");
            
            return $this->redirectToRoute('admin_articles');
        }
        return $this->renderForm("admin/admin_edit_article.html.twig", [ 
            "formArticles" => $form,
            "editArticle" => $article->getId() !== null
        ]);
    }
    #[Route('/admin/article/delete/{id}', name:'admin_delete_article')]

    public function deleteArticle(Article $article, EntityManagerInterface $manager)
    {

        $manager->remove($article);
        $manager->flush();
        $this->addFlash('success', "L'article a bien été supprimé");
        return $this->redirectToRoute('admin_articles');
    }

    #[Route('/admin/produit/show/{id}', name: 'admin_show_article')]
    public function showAticles($id, ArticleRepository $repo, Request $globals, EntityManagerInterface $manager)  //$id correspond au {id} (paramètres de route) dans l'URL 
    {
        $articles = $repo->find($id);

        return $this->renderForm('admin/admin_show_article.html.twig', [
            'articles' => $articles
        ]);

    }

    /** CRUD CATEGORY */
    #[Route('/admin/category', name: 'admin_category')]
    public function categ(CategoryRepository $repo)
    {
        $categories = $repo->findAll();
     
        return $this->renderForm("admin/admin_category.html.twig", [
            "categories" => $categories,
        ]);
    }
    
    #[Route('/admin/category/create', name: "admin_create_category")]
    public function createCategory(Request $globals, EntityManagerInterface $manager, Category $category = null)
    {
        if($category == null):
            $category = new Category;
        endif;


        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($globals);



        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($category);
            $manager->flush();
            $this->addFlash('success', "L'article a bien été enregistré !");
            
            return $this->redirectToRoute('admin_category');
        }
        return $this->renderForm("admin/admin_create_category.html.twig", [
            "formCategory" => $form
        ]);
    }
    /** CRUD TRANSPORTEUR */
    #[Route('/admin/carrier', name: 'admin_carrier')]
    public function carrier(CarrierRepository $repo)
    {
        $carriers = $repo->findAll();
     
        return $this->renderForm("admin/admin_carrier.html.twig", [
            "carriers" => $carriers,
        ]);
    }
    
    #[Route('/admin/carrier/create', name: "admin_create_carrier")]
    public function createCarrier(Request $globals, EntityManagerInterface $manager, Carrier $carrier = null)
    {
        if($carrier == null):
            $carrier = new Carrier;
        endif;


        $form = $this->createForm(CarrierType::class, $carrier);

        $form->handleRequest($globals);



        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($carrier);
            $manager->flush();
            $this->addFlash('success', "Le transporteur a bien été enregistré !");
            
            return $this->redirectToRoute('admin_carrier');
        }
        return $this->renderForm("admin/admin_create_carrier.html.twig", [
            "formCarrier" => $form
        ]);
    }
    #[Route('admin/carrier/edit/{id}', name: 'admin_edit_carrier')]
    public function edit(Request $request, EntityManagerInterface $manager, $id): Response
    {
        $carrier = $manager->getRepository(Carrier::class)->findOneById($id);

        $form = $this->createForm(CarrierType::class, $carrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->flush();
            $this->addFlash('success', "Le transporteur a bien été enregistré !");
            
            return $this->redirectToRoute('admin_carrier');
        }

        return $this->render('admin/admin_create_carrier.html.twig', [
            'formCarrier' => $form->createView()
        ]);
    }
    #[Route('admin/carrier/delete/{id}', name: 'admin_delete_carrier')]
    public function delete($id, EntityManagerInterface $manager): Response
    {
        $carrier = $manager->getRepository(Carrier::class)->findOneById($id);

        // if ($adress && $adress->getMembre() == $this->getUser()) {
        //     return $this->redirectToRoute('app_account');
        // }
        $manager->remove($carrier);
        $manager->flush();
        $this->addFlash('success', "Le transporteur a bien été supprimé !");

        return $this->redirectToRoute('admin_carrier');
    }
}
