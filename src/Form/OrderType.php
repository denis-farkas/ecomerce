<?php
namespace App\Form;

use App\Entity\Address;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('addresses', EntityType::class, [
                'class' => Address::class,
                'choices' => $options['addresses'],
                'choice_label' => function (Address $address) {
                    return $address->getAddress() . ', ' . $address->getPostal() . ', ' . $address->getCity().', '.$address->getCountry();
                },
                'multiple' => false,
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'addresses' => [],
        ]);
    }
}