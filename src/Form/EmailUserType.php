<?php

 

namespace App\Form;

 

use App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\FormError;

use Symfony\Component\Form\FormEvent;

use Symfony\Component\Form\FormEvents;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\NotBlank;

 

class EmailUserType extends AbstractType

{

    public function buildForm(FormBuilderInterface $builder, array $options): void

    {
        $user = $options['data']; // This is your User object
    
        // Now you can access user properties
        $userEmail = $user->getEmail();
        $emailPlaceholder = ($user && $user->getEmail()) 
        ? $user->getEmail() 
        : 'Saisir votre Email';


        $builder

        ->add('actualEmail', EmailType::class, [

            'label' => 'Email actuel',

            'attr' => ['placeholder' => $emailPlaceholder],

            'mapped' => false ,

            "disabled" => true

        ])

        ->add('email', EmailType::class, [
            'label' => 'Email',
            'required' => true,
            'constraints' => [
                new NotBlank(["message" => "L'email ne peut pas être vide."]),
                new \Symfony\Component\Validator\Constraints\Email([
                    'message' => 'L\'adresse email "{{ value }}" n\'est pas valide.',
                    'mode' => 'html5'  // Change to strict to enforce proper validation
                ])
            ],
            'attr' => [
                'placeholder' => 'Saisir votre Email',
                'pattern' => '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$'
            ]   
        ])

             ->add('submit', SubmitType::class, [

                'label' => 'Modifier votre email', 'attr' => ['class' => 'btn btn-success']

            ])

           

        ;

    }
 

    public function configureOptions(OptionsResolver $resolver): void

    {

        $resolver->setDefaults([

            'data_class' => User::class,

        ]);

    }

}