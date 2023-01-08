<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\OrderDetails;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{

    #[Route('/order', name: 'app_order')]
    public function index(CartService $cart): Response
    {
        if(!$this->getUser()->getAdresses()->getValues()) {
            return $this->redirectToRoute('app_adress');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);
        
        return $this->render('order/index.html.twig', [
            'formOrder' => $form->createView(),
            'cart' => $cart->getCartWithData()
        ]);
    }
    #[Route('/order/recap', name: 'order_recap')]
    public function add(CartService $cart, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $date = new \DateTime;
            $carriers = $form->get('carriers')->getData();
            $delivery = $form->get('addresses')->getData();

            $deliveryContent = $delivery->getName(). ' ' . $delivery->getFirstName().' '. $delivery->getLastName();
            $deliveryContent .= '<br/>' . $delivery->getAdress();
            $deliveryContent .= '<br/>' . $delivery->getPostal() . ' '. $delivery->getCity();
            $deliveryContent .= '<br/>' . $delivery->getCountry();

            //Enregistrer ma commande
            $order = new Order();
            $reference= $date->format('dmY').'-'.uniqid();
            $order->setReference($reference);
            $order->setUser($this->getUser());
            $order->setCreatedAt($date);
            $order->setCarrierName($carriers->getName());
            $order->setCarrierPrice($carriers->getPrice());
            $order->setDelivery($deliveryContent);
            $order->setIsPaid(1);
            $total = $cart->getTotal();
            $manager->persist($order);

            foreach($cart->getCartWithData() as $product) {
                
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order);
                $orderDetails->setProduct($product['produit']->getTitre());
                $orderDetails->setQuantity($product['quantite']);
                $orderDetails->setPrice($product['produit']->getPrix());
                $orderDetails->setTotal($product['produit']->getPrix() * $product['quantite'] );
                $manager->persist($orderDetails);

                
            }
            
            $this->addFlash('success', "La commande a bien été enregistré !");

            $manager->flush();
            
           
            //   dump();
            //dd($checkout_session);

            return $this->render('order/add.html.twig', [
                'cart' => $cart->getCartWithData(),
                'total' => $total,
                'carrier' =>$carriers,
                'delivery' => $deliveryContent,
                'reference' => $order->getReference()
            ]);
        }
        return $this->render('order/add.html.twig', [
            'cart' => $cart->getCartWithData(),
        ]);
    }
}