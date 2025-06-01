<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

     #[Route('/products', name: 'app_product')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        
        // Serialize products to pass to React
        $serializedProducts = [];
        foreach ($products as $product) {
            $serializedProducts[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'image1' => $product->getImage1(),
                'createdAt' => $product->getCreatedAt(), 
                // Add any other fields you need
            ];
        }
        
        return $this->render('product/index.html.twig', [
            'products' => $serializedProducts
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_by_id')]
    public function ficheProduit($id,EntityManagerInterface $entityManager): Response
    {

    $product = $entityManager->getRepository(Product::class)->find($id) ;

        return $this->render('product/fiche_produit.html.twig', [
            'product' => $product
        ]);
    }
}
