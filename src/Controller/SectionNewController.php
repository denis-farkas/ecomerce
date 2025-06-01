<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SectionNewController extends AbstractController
{
    #[Route('/section_new', name: 'app_section_new')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager -> getRepository(Product :: class)-> findByRecent();
        return $this->render('section_new/index.html.twig', [
            'products' => $products,
        ]);
    }
}
