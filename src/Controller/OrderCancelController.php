<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderCancelController extends AbstractController
{
    #[Route('/order/erreur/{stripeSessionId}', name: 'order_cancel')]

    public function index(EntityManagerInterface $manager, $stripeSessionId): Response
    {
        $order = $manager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);



        return $this->render('order_cancel/index.html.twig', [
            'order' => $order
        ]);
    }
}
