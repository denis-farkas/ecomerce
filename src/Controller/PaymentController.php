<?php

namespace App\Controller;

use App\Entity\User;
use App\Classe\Cart;
use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\StockManager;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    public function __construct(private StockManager $stockManager)
    {
    }

    #[Route('/commande/paiement/{id_order}', name: 'app_payment')]
    public function index($id_order, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $order = $orderRepository->findOneBy([
            'id' => $id_order,
            'user' => $this->getUser()
        ]);

        if (!$order) {
            return $this->redirectToRoute('app_home');
        }

        // Vérifier la disponibilité du stock avant le paiement
        $unavailableProducts = [];
        foreach ($order->getOrderDetails() as $orderDetail) {
            $product = $entityManager->getRepository(\App\Entity\Product::class)
                ->findOneBy(['name' => $orderDetail->getProductName()]);
            
            if ($product && $product->getAvailableQuantity() < $orderDetail->getProductQuantity()) {
                $unavailableProducts[] = [
                    'name' => $product->getName(),
                    'requested' => $orderDetail->getProductQuantity(),
                    'available' => $product->getAvailableQuantity()
                ];
            }
        }

        // Si stock insuffisant, rediriger avec erreur
        if (!empty($unavailableProducts)) {
            foreach ($unavailableProducts as $unavailable) {
                $this->addFlash('error', sprintf(
                    'Stock insuffisant pour %s : %d demandé(s), %d disponible(s)',
                    $unavailable['name'],
                    $unavailable['requested'],
                    $unavailable['available']
                ));
            }
            return $this->redirectToRoute('cart');
        }

        // Réserver le stock pendant le processus de paiement
        $this->reserveStockForOrder($order, $entityManager);

        $products_for_stripe = [];

        foreach ($order->getOrderDetails() as $product) {
            $products_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => number_format($product->getProductPrice() * 100, 0, '', ''),
                    'product_data' => [
                        'name' => $product->getProductName(),
                        'images' => [
                            $_ENV['DOMAIN'].'/build/images/'.$product->getProductIllustration()
                        ]
                    ]
                ],
                'quantity' => $product->getProductQuantity(),
            ];
        }

        /** @var User $user */
        $user = $this->getUser();
        
        $checkout_session = Session::create([
            'customer_email' => $user->getEmail(),
            'line_items' => $products_for_stripe,
            'mode' => 'payment',
            'success_url' => $_ENV['DOMAIN'] . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $_ENV['DOMAIN'] . '/commande/annulation/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId($checkout_session->id);
        $entityManager->flush();

        return $this->redirect($checkout_session->url);
    }

    #[Route('/commande/merci/{stripe_session_id}', name: 'app_payment_success')]
    public function success($stripe_session_id, OrderRepository $orderRepository, EntityManagerInterface $entityManager, Cart $cart, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new \LogicException('User is not authenticated.');
        }

        $order = $orderRepository->findOneBy([
            'stripe_session_id' => $stripe_session_id,
            'user' => $user
        ]);

        if (!$order) {
            return $this->redirectToRoute('app_home');
        }

        if ($order->getState() == 1) {
            // Paiement réussi : confirmer la vente et déduire le stock définitivement
            $this->confirmStockForOrder($order, $entityManager);
            
            $order->setState(2);
            $cart->reset();
            $entityManager->flush();
        }

        return $this->render('payment/success.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/commande/annulation/{stripe_session_id}', name: 'app_payment_cancel')]
    public function cancel($stripe_session_id, OrderRepository $orderRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        if ($user) {
            $order = $orderRepository->findOneBy([
                'stripe_session_id' => $stripe_session_id,
                'user' => $user
            ]);

            if ($order && $order->getState() == 1) {
                // Paiement annulé : libérer le stock réservé
                $this->releaseReservedStockForOrder($order, $entityManager);
            }
        }

        $this->addFlash('error', 'Votre paiement a été annulé.');
        
        return $this->render('payment/cancel.html.twig', [
            'message' => 'Le paiement a été annulé',
        ]);
    }

    private function reserveStockForOrder(Order $order, EntityManagerInterface $entityManager): void
    {
        foreach ($order->getOrderDetails() as $orderDetail) {
            $product = $entityManager->getRepository(\App\Entity\Product::class)
                ->findOneBy(['name' => $orderDetail->getProductName()]);
            
            if ($product) {
                $this->stockManager->reserveStock(
                    $product, 
                    $orderDetail->getProductQuantity()
                );
            }
        }
    }

    private function confirmStockForOrder(Order $order, EntityManagerInterface $entityManager): void
    {
        foreach ($order->getOrderDetails() as $orderDetail) {
            $product = $entityManager->getRepository(\App\Entity\Product::class)
                ->findOneBy(['name' => $orderDetail->getProductName()]);
            
            if ($product) {
                $this->stockManager->releaseStock(
                    $product, 
                    $orderDetail->getProductQuantity()
                );
            }
        }
    }

    private function releaseReservedStockForOrder(Order $order, EntityManagerInterface $entityManager): void
    {
        foreach ($order->getOrderDetails() as $orderDetail) {
            $product = $entityManager->getRepository(\App\Entity\Product::class)
                ->findOneBy(['name' => $orderDetail->getProductName()]);
            
            if ($product && $product->getStock()) {
                $stock = $product->getStock();
                $stock->setReserved(max(0, $stock->getReserved() - $orderDetail->getProductQuantity()));
                $stock->setUpdatedAt(new \DateTimeImmutable());
                $entityManager->flush();
            }
        }
    }
}