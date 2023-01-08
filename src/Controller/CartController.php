<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Service\CartService;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{   

    #[Route('/cart', name: 'cart')]
    public function index(CartService $cs): Response
    {
        $cartWithData = $cs->getCartWithData();
        
        $total = $cs->getTotal();

        return $this->renderForm('cart/index.html.twig', [
            'cart' =>$cartWithData,
            'total' =>$total
        ]);
    }
    #[Route('/cart/add/{id}', name:'cart_add')]
    public function add($id, CartService $cs, Request $request, ProduitRepository $repo) // la classe request stack permet de récupérer la session 
    {
        $quantite = $request->request->get("addQuantite");
        $cs->add($id, $quantite);
        $produit = $repo->find($id);
        $titre = $produit->getTitre();
        $this->addFlash('success', "ajout de $quantite $titre dans votre panier");
         return $this->redirectToRoute('cart');
    }
    #[Route("/cart/remove/{id}", name:"cart_remove")]
    public function remove($id, CartService $cs)
    {
        $cs->remove($id);
        return $this->redirectToRoute('cart');
    }
    #[Route("/cart/adding/{id}", name:"cart_adding")]
    public function adding($id, CartService $cs) 
    {
        $cs->adding($id);        
        return $this->redirectToRoute('cart');
    }
    #[Route("/cart/decrease/{id}", name:"cart_decrease")]
    public function decrease($id, CartService $cs) 
    {
        $cs->decrease($id);        
        return $this->redirectToRoute('cart');
    }

    // #[Route('/killsession', name:'app_unsession')]
    // public function kill( RequestStack $rs)
    // {
    //     $session = $rs->getSession();
    //     $session->clear();
    // }

    
}
// namespace App\Controller;

// use App\Class\Cart;
// use App\Entity\Produit;
// use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// class CartController extends AbstractController
// {
//     private $entityManager;

//     public function __construct(EntityManagerInterface $entityManager)
//     {
        
//         $this->entityManager = $entityManager;
//     }

//     #[Route('/cart', name: 'app_cart')]
//     public function index(Cart $cart): Response
//     {
//         $cartComplete = [];

//         foreach ($cart->get() as $id => $quantity) {
//             $cartComplete[] = [
//                 'produit' => $this->entityManager->getRepository(Produit::class)->findOneById($id),
//                 'quantity' => $quantity
//             ];
//         }
//         $total = $cart->getTotal();
//         return $this->render('cart/index.html.twig', [
//             'cart' => $cartComplete,
//             'total' => $total
//         ]);
//     }

//     #[Route('/cart/add/{id}', name: 'add_cart')]
//     public function add(Cart $cart, $id): Response
//     {

//         $cart->add($id);
//         return $this->redirectToRoute('app_cart');
//     }

//     #[Route('/cart/remove', name: 'remove_cart')]
//     public function remove(Cart $cart): Response
//     {

//         $cart->remove();
//         return $this->redirectToRoute('app_shop');
//     }
// }
