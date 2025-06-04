<?php


namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    public function __construct(private LoggerInterface $cartLogger)
    {
    }

    #[Route('/add-to-cart/{id}', name: 'add_to_cart', methods: ['POST'])]
    public function addToCart($id, Request $request, Cart $cart, EntityManagerInterface $entityManager): Response
    {
        $this->cartLogger->info('Cart: Attempting to add product to cart', [
            'product_id' => $id,
            'user_identifier' => $this->getUser()?->getUserIdentifier()
        ]);

        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            $this->cartLogger->warning('Cart: Product not found', ['product_id' => $id]);
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $quantity = (int)$request->request->get('quantity', 1);

        $this->cartLogger->info('Cart: Product found, checking stock', [
            'product_id' => $product->getId(),
            'product_name' => $product->getName(),
            'requested_quantity' => $quantity,
            'available_quantity' => $product->getAvailableQuantity(),
            'in_stock' => $product->isInStock()
        ]);

        // Vérifier la disponibilité du stock
        if (!$product->isInStock() || $product->getAvailableQuantity() < $quantity) {
            $this->cartLogger->warning('Cart: Insufficient stock', [
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
                'requested_quantity' => $quantity,
                'available_quantity' => $product->getAvailableQuantity()
            ]);

            $this->addFlash('error', sprintf(
                'Stock insuffisant pour %s. Disponible : %d',
                $product->getName(),
                $product->getAvailableQuantity()
            ));
            return $this->redirectToRoute('app_product_by_id', ['id' => $id]);
        }

        $cart->add($product, $quantity);
        
        $this->cartLogger->info('Cart: Product successfully added to cart', [
            'product_id' => $product->getId(),
            'product_name' => $product->getName(),
            'quantity_added' => $quantity,
            'cart_total_quantity' => $cart->getTotalQuantity(),
            'cart_total_price' => $cart->getTotalPrice()
        ]);

        $this->addFlash('success', 'Produit ajouté au panier.');

        return $this->redirectToRoute('app_product');
    }

    #[Route('/cart', name: 'cart')]
    public function index(Cart $cart): Response
    {
        // Récupérer le panier
        $cartItems = $cart->getCart();
        $totalPrice = $cart->getTotalPrice();
        $totalQuantity = $cart->getTotalQuantity();

        $this->cartLogger->info('Cart: Displaying cart page', [
            'user_identifier' => $this->getUser()?->getUserIdentifier(),
            'cart_items_count' => count($cartItems),
            'total_quantity' => $totalQuantity,
            'total_price' => $totalPrice
        ]);

        return $this->render('boutique/cart.html.twig', [
            'cart' => $cartItems,
            'totalPrice' => $totalPrice,
            'totalQuantity' => $totalQuantity
        ]);
    }

    #[Route('/decrease-quantity/{id}', name: 'decrease_quantity')]
    public function decreaseQuantity($id, Cart $cart, EntityManagerInterface $entityManager): Response
    {
        $this->cartLogger->info('Cart: Attempting to decrease product quantity', [
            'product_id' => $id,
            'user_identifier' => $this->getUser()?->getUserIdentifier()
        ]);

        $product = $entityManager->getRepository(Product::class)->find($id);
        
        if (!$product) {
            $this->cartLogger->warning('Cart: Product not found for decrease quantity', ['product_id' => $id]);
            throw $this->createNotFoundException('Produit non trouvé.');
        }
        
        $cart->decreaseQuantity($product);
        
        $this->cartLogger->info('Cart: Product quantity decreased', [
            'product_id' => $product->getId(),
            'product_name' => $product->getName(),
            'cart_total_quantity' => $cart->getTotalQuantity()
        ]);
        
        return $this->redirectToRoute('cart');
    }

    #[Route('/increase-quantity/{id}', name: 'increase_quantity')]
    public function increaseQuantity($id, Cart $cart, EntityManagerInterface $entityManager): Response
    {
        $this->cartLogger->info('Cart: Attempting to increase product quantity', [
            'product_id' => $id,
            'user_identifier' => $this->getUser()?->getUserIdentifier()
        ]);

        $product = $entityManager->getRepository(Product::class)->find($id);
        
        if (!$product) {
            $this->cartLogger->warning('Cart: Product not found for increase quantity', ['product_id' => $id]);
            throw $this->createNotFoundException('Produit non trouvé.');
        }
        
        $cart->increaseQuantity($product);
        
        $this->cartLogger->info('Cart: Product quantity increased', [
            'product_id' => $product->getId(),
            'product_name' => $product->getName(),
            'cart_total_quantity' => $cart->getTotalQuantity()
        ]);
        
        return $this->redirectToRoute('cart');
    }

    #[Route('/remove-from-cart/{id}', name: 'remove_from_cart')]
    public function removeFromCart($id, Cart $cart, EntityManagerInterface $entityManager): Response
    {
        $this->cartLogger->info('Cart: Attempting to remove product from cart', [
            'product_id' => $id,
            'user_identifier' => $this->getUser()?->getUserIdentifier()
        ]);

        // Récupérer le produit à partir de son ID
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            $this->cartLogger->warning('Cart: Product not found for removal', ['product_id' => $id]);
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $cart->remove($product);

        $this->cartLogger->info('Cart: Product removed from cart', [
            'product_id' => $product->getId(),
            'product_name' => $product->getName(),
            'cart_total_quantity' => $cart->getTotalQuantity()
        ]);

        // Ajouter un message flash de succès
        $this->addFlash('success', 'Produit retiré du panier.');

        // Rediriger vers la page panier
        return $this->redirectToRoute('cart');
    }
}