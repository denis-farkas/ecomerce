<?php


namespace App\Controller\Admin;


use App\Entity\Contact;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;


class ContactCrudController extends AbstractCrudController

{

    public static function getEntityFqcn(): string

    {

        return Contact::class;

    }


    public function configureActions(Actions $actions): Actions

    {

        return $actions

            ->remove(Action::INDEX, Action::NEW);

    }

    

    public function configureFields(string $pageName): iterable

    {

        return [

            DateTimeField::new('createdAt')

                ->setFormTypeOptions([

                    'widget' => 'single_text',

                    'html5' => true,

                    'required' => false,

                ])

                ->setFormat('yyyy-MM-dd HH:mm:ss')

                ->setFormTypeOption('input', 'datetime_immutable')

                ->setLabel('Date'),

            TextField::new('name')->setLabel('Nom'),    

            TextField::new('message')->setLabel('Message')

                ->setFormTypeOptions([

                'attr' => ['rows' => 10],

            ])

            ->setTemplatePath('admin/fields/textarea.html.twig'), // Optional: Custom template for better display,

            TextField::new('email')->setLabel('Email'),

        ];

    }

    

}