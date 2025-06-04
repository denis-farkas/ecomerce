<?php


namespace App\Classe;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Cart
{
    private float $totalPrice = 0.0;
    private LoggerInterface $logger;

    public function __construct(private RequestStack $requestStack, LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function add($product, $quantity)
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        $id = $product->getId();

        $this->logger->info('Cart Service: Adding product to cart', [
            'product_id' => $id,
            'product_name' => $product->getName(),
            'quantity' => $quantity,
            'product_price' => $product->getPrice()
        ]);

        if (empty($cart[$id])) {
            $cart[$product->getId()] = [
                'object' => $product,
                'qty' => $quantity
            ];
            $this->logger->info('Cart Service: New product added to cart', [
                'product_id' => $id,
                'initial_quantity' => $quantity
            ]);
        } else {
            $oldQuantity = $cart[$id]['qty'];
            $cart[$id]['qty'] += $quantity;
            $this->logger->info('Cart Service: Product quantity updated in cart', [
                'product_id' => $id,
                'old_quantity' => $oldQuantity,
                'added_quantity' => $quantity,
                'new_quantity' => $cart[$id]['qty']
            ]);
        }

        $this->requestStack->getSession()->set('cart', $cart);

        // ajoute le prix du produit au prix total
        $this->totalPrice += $product->getPrice() * $cart[$id]['qty'];

        $this->logger->info('Cart Service: Cart updated', [
            'cart_total_items' => count($cart),
            'cart_total_quantity' => $this->getTotalQuantity(),
            'cart_total_price' => $this->getTotalPrice()
        ]);
    }

    public function increaseQuantity($product)
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        $id = $product->getId();
        
        $this->logger->info('Cart Service: Increasing product quantity', [
            'product_id' => $id,
            'product_name' => $product->getName(),
            'current_quantity' => $cart[$id]['qty'] ?? 0
        ]);

        $cart[$id]['qty']++;
        $this->requestStack->getSession()->set('cart', $cart);

        $this->logger->info('Cart Service: Product quantity increased', [
            'product_id' => $id,
            'new_quantity' => $cart[$id]['qty']
        ]);
    }

    public function decreaseQuantity($product)
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        $id = $product->getId();
        
        $this->logger->info('Cart Service: Decreasing product quantity', [
            'product_id' => $id,
            'product_name' => $product->getName(),
            'current_quantity' => $cart[$id]['qty'] ?? 0
        ]);

        if ($cart[$id]['qty'] > 1) {
            $cart[$id]['qty']--;
            $this->logger->info('Cart Service: Product quantity decreased', [
                'product_id' => $id,
                'new_quantity' => $cart[$id]['qty']
            ]);
        } else {
            unset($cart[$id]);
            $this->logger->info('Cart Service: Product removed from cart (quantity was 1)', [
                'product_id' => $id
            ]);
        }

        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function remove($product)
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        $id = $product->getId();

        $this->logger->info('Cart Service: Removing product from cart', [
            'product_id' => $id,
            'product_name' => $product->getName(),
            'quantity_removed' => $cart[$id]['qty'] ?? 0
        ]);

        unset($cart[$id]);
        $this->requestStack->getSession()->set('cart', $cart);

        $this->logger->info('Cart Service: Product removed from cart', [
            'product_id' => $id,
            'remaining_cart_items' => count($cart)
        ]);
    }

    public function getTotalQuantity()
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        $quantity = 0;

        foreach ($cart as $product) {
            $quantity += $product['qty'];
        }

        return $quantity;
    }

    public function getTotalPrice()
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        $price = 0;

        foreach ($cart as $product) {
            $price += $product['object']->getPrice() * $product['qty'];
        }

        return $price;
    }

    public function getCart()
    {
        return $this->requestStack->getSession()->get('cart', []);
    }

    public function removeAll()
    {
        $cartItemsCount = count($this->getCart());
        
        $this->logger->info('Cart Service: Clearing entire cart', [
            'items_removed' => $cartItemsCount
        ]);

        $this->requestStack->getSession()->remove('cart');

        $this->logger->info('Cart Service: Cart cleared successfully');
    }

    // Méthode manquante pour le processus de paiement
    public function reset()
    {
        $cartItemsCount = count($this->getCart());
        
        $this->logger->info('Cart Service: Resetting cart after payment', [
            'items_removed' => $cartItemsCount
        ]);

        $this->requestStack->getSession()->remove('cart');
        $this->totalPrice = 0.0;

        $this->logger->info('Cart Service: Cart reset successfully after payment');
    }
}