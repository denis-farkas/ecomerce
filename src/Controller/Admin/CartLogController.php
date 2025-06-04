<?php


namespace App\Controller\Admin;

use App\Entity\CartLog;
use App\Repository\CartLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/cart-logs')]
#[IsGranted('ROLE_ADMIN')]
class CartLogController extends AbstractController
{
    #[Route('/', name: 'admin_cart_logs')]
    public function index(CartLogRepository $cartLogRepository, Request $request): Response
    {
        $level = $request->query->get('level');
        $userIdentifier = $request->query->get('user');
        $productId = $request->query->get('product');

        if ($level) {
            $logs = $cartLogRepository->findByLevel($level);
        } elseif ($userIdentifier) {
            $logs = $cartLogRepository->findByUser($userIdentifier);
        } elseif ($productId) {
            $logs = $cartLogRepository->findByProduct((int)$productId);
        } else {
            $logs = $cartLogRepository->findRecentLogs(200);
        }

        $statistics = $cartLogRepository->getStatistics();

        return $this->render('admin/cart_logs/index.html.twig', [
            'logs' => $logs,
            'statistics' => $statistics,
            'current_filter' => [
                'level' => $level,
                'user' => $userIdentifier,
                'product' => $productId
            ]
        ]);
    }

    #[Route('/{id}', name: 'admin_cart_log_detail')]
    public function detail(CartLog $cartLog): Response
    {
        return $this->render('admin/cart_logs/detail.html.twig', [
            'log' => $cartLog
        ]);
    }

    #[Route('/clear/all', name: 'admin_cart_logs_clear', methods: ['POST'])]
    public function clearLogs(CartLogRepository $cartLogRepository): Response
    {
        $qb = $cartLogRepository->createQueryBuilder('cl');
        $qb->delete()->getQuery()->execute();

        $this->addFlash('success', 'Tous les logs du panier ont été supprimés.');

        return $this->redirectToRoute('admin_cart_logs');
    }
}