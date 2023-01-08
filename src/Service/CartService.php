<?php

namespace App\Service;

use Doctrine\ORM\Query\Expr\Func;

use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private $repo;
    private $rs;

    public function __construct(ProduitRepository $repo, RequestStack $rs)
    {
        $this->repo = $repo;
        $this->rs = $rs;
    }

    public function add($id, $addQuantite)
    {
        $session = $this->rs->getSession();
     
        $cart = $session->get('cart', []); // si je récupère l'attr de session 'cart' s'il existe ou un tableau vide

        //si le produit exite déjà, j'incrémente sa quantité
        if(!empty($cart[$id])){
            if($addQuantite > 1)
            {
                $cart[$id] += $addQuantite;
            }
            else{
              $cart[$id]++;  
            }
            
        }
        else {
            $cart[$id] = $addQuantite; //si dans mon tableau $cart, à la case $id, j'insère la valeur 1
        }
        

        $session->set('cart', $cart); // je sauvegarde l'étaat de mon panier en session à l'attr de session 'cart'
    }

    public function remove($id)
    {
        $session = $this->rs->getSession();
        $cart = $session->get('cart', []);

        if(!empty($cart[$id]))
        {
            unset($cart[$id]);
        }
        $session->set('cart', $cart);

    }
    public function getCartWithData()
    {
        $session = $this->rs->getSession();
        $cart = $session->get('cart', []);

        //nous allons créer un nouveau tableau qui conteindrat des objet de la classe produit et les quantitées de chaque objet
        $cartWithData = [];

        foreach($cart as $id => $quantite){
            $cartWithData[] = [
                'produit' => $this->repo->find($id),
                'quantite' => $quantite
            ];
        }
        return $cartWithData;
    }
    public function getTotal()
    {
        $cartWithData = $this->getCartWithData();
        $total= 0;
        // dd($cartWithData);
        foreach($cartWithData as $item){
            $totalUnitaire = $item['produit']->getPrix() * $item['quantite'];
            $total += $totalUnitaire;

        }
        return $total;
    }

    public function decrease($id)
    {
        $session = $this->rs->getSession();
        $cart = $session->get('cart', []);
 
        if ($cart[$id] > 1) 
        {
            $cart[$id]--;
        } else {
 
            unset($cart[$id]);
             
        }
        return $session->set('cart', $cart);
    }

    public function adding($id)
    {
        $session = $this->rs->getSession();

        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) 
        {
            $cart[$id]++;
        }
        else
        {
            $cart[$id] = 1;
        }

        $session->set('cart', $cart);
    }



}