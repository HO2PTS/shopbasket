<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Produit;
use App\Service\MailService;
use App\Repository\ProduitRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }
    
    #[Route('/shop', name: 'app_shop')]
    public function shop(ProduitRepository $repo,  ): Response
    {
        $produits = $repo->findAll();
        
    

        return $this->render('home/shop.html.twig', [
            'produits' => $produits,
            
        ]);
    }
    #[Route('/shop/{name}', name: 'app_category')]
    public function shopCategory(ProduitRepository $repo, Category $category = null): Response
    {
        $produit = $repo->findBy(['category' => $category]);
    
        

        return $this->render('home/shop.html.twig', [
            'produits' => $produit
        ]);
    }

    #[Route('/shop/show/{id}', name: 'show_product')]
    public function show(EntityManagerInterface $manager,$id)
    {
        $product = $manager->getRepository(Produit::class)->findById($id);
        
        return $this->render('home/show_product.html.twig', [
            'produit' => $product
        ]);
    }
}
