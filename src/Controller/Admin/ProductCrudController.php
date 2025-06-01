<?php


namespace App\Controller\Admin;


use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class ProductCrudController extends AbstractCrudController

{

    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable

    {

        return [

            IdField::new('id')->onlyOnIndex(),
            TextField::new('name'),
            TextareaField::new('description'),
            NumberField::new('price'),
            DateTimeField::new('createdAt')->setFormTypeOptions([
                'html5' => true,
                'widget' => 'single_text',
            ]),
            AssociationField::new('famille')
            ->setLabel('Famille')
            ->formatValue(function ($value, $entity) {
            return $value ? $value->getName() : '';
            }),

            TextField::new('image1')->onlyOnIndex(),
            TextField::new('image2')->onlyOnIndex(),

            ImageField::new('image1')
                ->setBasePath('/build/images')
                ->hideOnIndex()
                ->hideOnForm(),
            ImageField::new('image2')
                ->setBasePath('/build/images')
                ->hideOnIndex()
                ->hideOnForm(),

                
            Field::new('image1File')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),

            Field::new('image2File')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),
        ];
    }
}