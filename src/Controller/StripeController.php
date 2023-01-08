<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Order;
use App\Service\CartService;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    #[Route('/commande/create-session/{reference}', name: 'stripe_create_session')]
    public function index(CartService $cart, $reference, EntityManagerInterface $entityManager): Response
    {
        
        $product_stripe = [];
        $domain = 'http://localhost:8000';

        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);


        foreach($cart->getCartWithData() as $product) {

            $product_stripe[] = [
                'price_data' => [
                  'currency' => 'eur',
                  'unit_amount' => $product['produit']->getPrix() * 100,
                  'product_data' => [
                    'name' => $product['produit']->getTitre()
                  ],
              ],
              'quantity' => $product['quantite'],
              ];
         }
        $product_stripe[] = [
          'price_data' => [
          'currency' => 'eur',
          'unit_amount' => $order->getCarrierPrice() * 100,
          'product_data' => [
              'name' => $order->getCarrierName()
            ],
          ],
          'quantity' => 1,
        ];

        Stripe::setApiKey('sk_test_51MGjy5F9M4Y2NwtVvNKtCtU7hODO0nkhisDT6sW1Zy6y3kcZRCI7tQl84xjCdTFCsozmth6wv1bLJQyJSRyUszPW00ah1CqIgG');
            

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [
                $product_stripe
            ],
            'mode' => 'payment',
            'success_url' => $domain. '/order/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $domain. '/order/erreur/{CHECKOUT_SESSION_ID}',
          ]);

          $order->setStripeSessionId($checkout_session->id);
          $entityManager->flush();
          
        $response = new JsonResponse(['id' => $checkout_session->id]);

        return $response;
    }
}
