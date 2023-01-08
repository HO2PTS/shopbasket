<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\CartService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderValidateController extends AbstractController
{
    #[Route('/order/merci/{stripeSessionId}', name: 'app_order_validate')]
    public function index($stripeSessionId, EntityManagerInterface $manager): Response
    {
        $order = $manager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);
        $mail = new MailService();
        $content = " Hello " . $order->getUser()->getPseudo() . "!" . ' ' . "Votre commande la boutique Française est bien validé ";
        $mail->send($order->getUser()->getEmail(), $order->getUser()->getPseudo(), 'Merci Pour votre commande !', $content);

        return $this->render('order_validate/index.html.twig', [
            'order' => $order
        ]);
    }
}
