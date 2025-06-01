<?php


namespace App\Entity;


use App\Repository\ProductRepository;

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


    #[ORM\Column(length: 255)]

    private ?string $image2 = null;


    #[ORM\Column]

    private ?float $price = null;


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

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }


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


    public function setImage2(?string $image2): static

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

        return $this->price;

    }


    public function setPrice(float $price): static

    {

        $this->price = $price;


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

}