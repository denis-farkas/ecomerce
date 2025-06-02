<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[Vich\Uploadable]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $image1 = null;

    #[ORM\Column(length: 255, nullable: true)]  // Ajout de nullable: true
    private ?string $image2 = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[Vich\UploadableField(mapping: 'product_images', fileNameProperty: 'image1')]
    private ?File $image1File = null;

    #[Vich\UploadableField(mapping: 'product_images', fileNameProperty: 'image2')]
    private ?File $image2File = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Famille $famille = null;

    /**
     * @var Collection<int, StockMovement>
     */
    #[ORM\OneToMany(targetEntity: StockMovement::class, mappedBy: 'product')]
    private Collection $stockMovements;

    #[ORM\OneToOne(mappedBy: 'product', cascade: ['persist', 'remove'])]
    private ?Stock $stock = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->stockMovements = new ArrayCollection();
    }

    // ... existing getters and setters ...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function setImage1File(?File $image1File = null): void
    {
        $this->image1File = $image1File;

        if (null !== $image1File) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImage1File(): ?File
    {
        return $this->image1File;
    }

    public function setImage1(?string $image1): static
    {
        $this->image1 = $image1;
        return $this;
    }

    public function getImage1(): ?string
    {
        return $this->image1;
    }

    public function setImage2File(?File $image2File = null): void
    {
        $this->image2File = $image2File;

        if (null !== $image2File) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImage2File(): ?File
    {
        return $this->image2File;
    }

    public function setImage2(?string $image2): static  // Nullable parameter
    {
        $this->image2 = $image2;
        return $this;
    }

    public function getImage2(): ?string
    {
        return $this->image2;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPrice(): ?float
    {
        return $this->price ? (float) $this->price : null;
    }

    public function setPrice(float $price): static
    {
        $this->price = (string) $price;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getFamille(): ?Famille
    {
        return $this->famille;
    }

    public function setFamille(?Famille $famille): static
    {
        $this->famille = $famille;
        return $this;
    }

    /**
     * @return Collection<int, StockMovement>
     */
    public function getStockMovements(): Collection
    {
        return $this->stockMovements;
    }

    public function addStockMovement(StockMovement $stockMovement): static
    {
        if (!$this->stockMovements->contains($stockMovement)) {
            $this->stockMovements->add($stockMovement);
            $stockMovement->setProduct($this);
        }

        return $this;
    }

    public function removeStockMovement(StockMovement $stockMovement): static
    {
        if ($this->stockMovements->removeElement($stockMovement)) {
            if ($stockMovement->getProduct() === $this) {
                $stockMovement->setProduct(null);
            }
        }

        return $this;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): static  // Nullable parameter
    {
        if ($stock === null && $this->stock !== null) {
            $this->stock->setProduct($this);
        }

        if ($stock !== null && $stock->getProduct() !== $this) {
            $stock->setProduct($this);
        }

        $this->stock = $stock;
        return $this;
    }

    public function isInStock(): bool
    {
        return $this->stock ? $this->stock->isInStock() : false;
    }

    public function getAvailableQuantity(): int
    {
        return $this->stock ? $this->stock->getAvailableQuantity() : 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock ? $this->stock->isLowStock() : false;
    }

    // Méthodes pour EasyAdmin
    public function getStockQuantity(): ?int
    {
        return $this->stock ? $this->stock->getQuantity() : null;
    }

    public function getStockMinQuantity(): ?int
    {
        return $this->stock ? $this->stock->getMinQuantity() : null;
    }

    public function isStockActive(): ?bool
    {
        return $this->stock ? $this->stock->isActive() : null;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}