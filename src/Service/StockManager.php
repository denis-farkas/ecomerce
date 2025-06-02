<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\Stock;
use App\Entity\StockMovement;
use App\Enum\MovementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class StockManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {}

    public function addStock(Product $product, int $quantity, string $reason = 'Manual addition'): void
    {
        $stock = $product->getStock();
        if (!$stock) {
            $stock = new Stock();
            $stock->setProduct($product);
            $product->setStock($stock);
        }

        $stock->setQuantity($stock->getQuantity() + $quantity);
        $stock->setUpdatedAt(new \DateTimeImmutable());

        $this->createMovement($product, MovementType::IN, $quantity, $reason);
        
        $this->entityManager->persist($stock);
        $this->entityManager->flush();
    }

    public function reserveStock(Product $product, int $quantity): bool
    {
        $stock = $product->getStock();
        if (!$stock || $stock->getAvailableQuantity() < $quantity) {
            return false;
        }

        $stock->setReserved($stock->getReserved() + $quantity);
        $stock->setUpdatedAt(new \DateTimeImmutable());

        $this->createMovement($product, MovementType::RESERVED, $quantity, 'Order reservation');
        
        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        return true;
    }

    public function releaseStock(Product $product, int $quantity): void
    {
        $stock = $product->getStock();
        if (!$stock) return;

        // Diminuer les réservations et le stock total
        $stock->setReserved(max(0, $stock->getReserved() - $quantity));
        $stock->setQuantity(max(0, $stock->getQuantity() - $quantity));
        $stock->setUpdatedAt(new \DateTimeImmutable());

        $this->createMovement($product, MovementType::OUT, $quantity, 'Order completed - Stock sold');
        
        $this->entityManager->persist($stock);
        $this->entityManager->flush();
    }

    public function removeStock(Product $product, int $quantity, string $reason = 'Manual removal'): bool
    {
        $stock = $product->getStock();
        if (!$stock || $stock->getQuantity() < $quantity) {
            return false;
        }

        $stock->setQuantity($stock->getQuantity() - $quantity);
        $stock->setUpdatedAt(new \DateTimeImmutable());

        $this->createMovement($product, MovementType::OUT, $quantity, $reason);
        
        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        return true;
    }

    public function checkStockAvailability(Product $product, int $requestedQuantity): bool
    {
        return $product->getAvailableQuantity() >= $requestedQuantity;
    }

    private function createMovement(Product $product, MovementType $type, int $quantity, string $reason): void
    {
        $movement = new StockMovement();
        $movement->setProduct($product);
        $movement->setType($type);
        $movement->setQuantity($quantity);
        $movement->setReason($reason);
        $movement->setCreatedAt(new \DateTime());
        $movement->setCreatedBy($this->security->getUser());

        $this->entityManager->persist($movement);
    }
}