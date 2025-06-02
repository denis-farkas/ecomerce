<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\Stock;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Produits')
            ->setEntityLabelInSingular('Produit')
            ->setPageTitle('index', 'Gestion des Produits')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // Informations de base
            IdField::new('id')->onlyOnIndex(),
            
            TextField::new('name', 'Nom du produit')
                ->setRequired(true)
                ->setColumns(6),
            
            TextareaField::new('description', 'Description')
                ->setRequired(true)
                ->setColumns(12)
                ->setNumOfRows(4),
            
            // CORRECTION: Utiliser NumberField au lieu de MoneyField
            NumberField::new('price', 'Prix (€)')
                ->setNumDecimals(2)
                ->setRequired(true)
                ->setColumns(6)
                ->setHelp('Prix en euros (ex: 16.00)'),
            
            AssociationField::new('famille', 'Famille')
                ->setRequired(false)
                ->setColumns(6),
            
            // Images
            TextField::new('image1', 'Image 1')->onlyOnIndex(),
            TextField::new('image2', 'Image 2')->onlyOnIndex(),
            
            ImageField::new('image1', 'Aperçu Image 1')
                ->setBasePath('/build/images')
                ->hideOnIndex()
                ->hideOnForm(),
                
            ImageField::new('image2', 'Aperçu Image 2')
                ->setBasePath('/build/images')
                ->hideOnIndex()
                ->hideOnForm(),
            
            Field::new('image1File', 'Télécharger Image 1')
                ->setFormType(VichImageType::class)
                ->onlyOnForms()
                ->setColumns(6),
                
            Field::new('image2File', 'Télécharger Image 2')
                ->setFormType(VichImageType::class)
                ->onlyOnForms()
                ->setColumns(6),
            
            // Champs de stock pour le formulaire
            IntegerField::new('stockQuantity', 'Quantité en stock')
                ->setFormTypeOption('mapped', false)
                ->setRequired(false)
                ->setHelp('Quantité initiale en stock (0 par défaut)')
                ->setColumns(4)
                ->onlyOnForms(),
                
            IntegerField::new('stockMinQuantity', 'Seuil d\'alerte')
                ->setFormTypeOption('mapped', false)
                ->setRequired(false)
                ->setHelp('Seuil minimum (5 par défaut)')
                ->setColumns(4)
                ->onlyOnForms(),
                
            BooleanField::new('stockActive', 'Stock actif')
                ->setFormTypeOption('mapped', false)
                ->setRequired(false)
                ->setHelp('Activer la gestion de stock')
                ->setColumns(4)
                ->onlyOnForms(),
            
            // Affichage du stock avec AssociationField
            AssociationField::new('stock', 'Informations Stock')
                ->formatValue(function ($value, Product $entity) {
                    $stock = $entity->getStock();
                    if (!$stock) {
                        return '❌ Aucun stock configuré';
                    }
                    
                    $quantity = $stock->getQuantity();
                    $available = $stock->getAvailableQuantity();
                    $reserved = $stock->getReserved();
                    $min = $stock->getMinQuantity();
                    $active = $stock->isActive() ? '✅' : '❌';
                    
                    $status = '';
                    if (!$stock->isActive()) {
                        $status = '🔒 Inactif';
                    } elseif ($available <= 0) {
                        $status = '🔴 Rupture';
                    } elseif ($quantity <= $min) {
                        $status = '🟡 Stock faible';
                    } else {
                        $status = '🟢 En stock';
                    }
                    
                    return sprintf(
                        '%s | Qty: %d | Dispo: %d | Rés: %d | Min: %d | Actif: %s',
                        $status,
                        $quantity,
                        $available,
                        $reserved,
                        $min,
                        $active
                    );
                })
                ->onlyOnIndex(),
            
            // Dates
            DateTimeField::new('createdAt', 'Créé le')
                ->hideOnForm()
                ->setFormat('dd/MM/yyyy HH:mm'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Product $product */
        $product = $entityInstance;
        
        // Définir les dates
        if (!$product->getCreatedAt()) {
            $product->setCreatedAt(new \DateTimeImmutable());
        }
        $product->setUpdatedAt(new \DateTimeImmutable());
        
        // Persister le produit d'abord
        parent::persistEntity($entityManager, $entityInstance);
        
        // Créer le stock
        $this->createStock($entityManager, $product);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Product $product */
        $product = $entityInstance;
        
        // Mettre à jour la date
        $product->setUpdatedAt(new \DateTimeImmutable());
        
        // Mettre à jour le produit
        parent::updateEntity($entityManager, $entityInstance);
        
        // Mettre à jour le stock
        $this->updateStock($entityManager, $product);
    }

    private function createStock(EntityManagerInterface $entityManager, Product $product): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        
        if (!$request) {
            // Créer un stock par défaut
            $this->createDefaultStock($entityManager, $product);
            return;
        }
        
        $formData = $request->request->all('Product') ?? [];
        
        $stockQuantity = isset($formData['stockQuantity']) && $formData['stockQuantity'] !== '' 
            ? (int) $formData['stockQuantity'] 
            : 0;
        $stockMinQuantity = isset($formData['stockMinQuantity']) && $formData['stockMinQuantity'] !== ''
            ? (int) $formData['stockMinQuantity'] 
            : 5;
        $stockActive = isset($formData['stockActive']) ? (bool) $formData['stockActive'] : true;

        $stock = new Stock();
        $stock->setProduct($product);
        $stock->setQuantity($stockQuantity);
        $stock->setReserved(0);
        $stock->setMinQuantity($stockMinQuantity);
        $stock->setActive($stockActive);
        $stock->setUpdatedAt(new \DateTimeImmutable());
        
        $entityManager->persist($stock);
        $entityManager->flush();
    }

    private function updateStock(EntityManagerInterface $entityManager, Product $product): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        
        if (!$request) {
            return;
        }
        
        $formData = $request->request->all('Product') ?? [];
        $stock = $product->getStock();
        
        if (!$stock) {
            $this->createStock($entityManager, $product);
            return;
        }
        
        // Mettre à jour seulement si les valeurs sont fournies
        if (isset($formData['stockQuantity']) && $formData['stockQuantity'] !== '') {
            $stock->setQuantity((int) $formData['stockQuantity']);
        }
        
        if (isset($formData['stockMinQuantity']) && $formData['stockMinQuantity'] !== '') {
            $stock->setMinQuantity((int) $formData['stockMinQuantity']);
        }
        
        if (isset($formData['stockActive'])) {
            $stock->setActive((bool) $formData['stockActive']);
        }
        
        $stock->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();
    }

    private function createDefaultStock(EntityManagerInterface $entityManager, Product $product): void
    {
        $stock = new Stock();
        $stock->setProduct($product);
        $stock->setQuantity(0);
        $stock->setReserved(0);
        $stock->setMinQuantity(5);
        $stock->setActive(true);
        $stock->setUpdatedAt(new \DateTimeImmutable());
        
        $entityManager->persist($stock);
        $entityManager->flush();
    }

    public function createEntity(string $entityFqcn)
    {
        return new Product();
    }
}