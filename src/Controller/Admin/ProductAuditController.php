<?php

namespace App\Controller\Admin;

use App\Entity\ProductAudit;
use App\Repository\ProductAuditRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/audit')]
class ProductAuditController extends AbstractController
{
    #[Route('/products', name: 'admin_product_audit')]
    public function index(ProductAuditRepository $repository): Response
    {
        $audits = $repository->findBy([], ['modifiedAt' => 'DESC'], 50);

        return $this->render('admin/audit/products.html.twig', [
            'audits' => $audits,
        ]);
    }
}