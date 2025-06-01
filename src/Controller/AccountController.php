<?php

namespace App\Controller;

use App\Form\EmailUserType;
use App\Form\PasswordUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }
    #[Route('/compte/modifier-mot-de-passe', name: 'app_account_modify_pwd')]

    public function password(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response

    {


        $user= $this->getUser();

        $form = $this->createForm(PasswordUserType::class, $user, ['passwordHasher' => $passwordHasher]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())

        {

            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès');

            return $this->redirectToRoute('app_account');

        }

        return $this->render('account/password.html.twig', ['modifyPwdForm' => $form->createView()]);

    }

#[Route('/compte/modifier-email', name: 'app_account_modify_email')]

    public function email(Request $request, EntityManagerInterface $entityManager): Response

    {


        $user= $this->getUser();

        $form = $this->createForm(EmailUserType::class, $user );

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())

        {

            $entityManager->flush();

            $this->addFlash('success', 'Votre email a été modifié avec succès');

            return $this->redirectToRoute('app_account');

        }

        return $this->render('account/email.html.twig', ['modifyEmailForm' => $form->createView()]);

    }
}