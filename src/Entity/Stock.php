<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'stock', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column]
    private ?int $quantity = 0;

    #[ORM\Column]
    private ?int $reserved = 0;

    #[ORM\Column]
    private ?int $minQuantity = 5;

    #[ORM\Column]
    private ?bool $isActive = true;

    // CORRECTION: Changer le type ici pour correspondre aux méthodes
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getReserved(): ?int
    {
        return $this->reserved;
    }

    public function setReserved(int $reserved): static
    {
        $this->reserved = $reserved;
        return $this;
    }

    public function getMinQuantity(): ?int
    {
        return $this->minQuantity;
    }

    public function setMinQuantity(int $minQuantity): static
    {
        $this->minQuantity = $minQuantity;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // Méthodes métier
    public function getAvailableQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved);
    }

    public function isInStock(): bool
    {
        return $this->getAvailableQuantity() > 0;
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->minQuantity;
    }
}