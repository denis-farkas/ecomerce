<?php
// filepath: c:\Users\denis\Desktop\quanti-mars\src\Controller\RegisterController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        MailerInterface $mailer
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterUserType::class, $user);
        $form->handleRequest($request);
       
        if($form->isSubmitted() && $form->isValid()){
            $user = $form->getData();
            $user->setRoles(['ROLE_USER']);
           
            // Persist the user
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();
           
            // Send welcome email
            $email = (new Email())
                ->from('noreply@demomailtrap.co')
                ->to($user->getEmail())
                ->subject('Bienvenue sur notre plateforme')
                ->html(
                    $this->renderView(
                        'emails/welcome.html.twig',
                        [
                            'user' => $user,
                        ]
                    )
                );
               
            $mailer->send($email);
           
            // Add flash message
            $this->addFlash('success', 'Votre compte a été créé avec succès. Un email de confirmation vous a été envoyé.');
           
            // Redirect to login page
            return $this->redirectToRoute('app_login');
        }
       
        return $this->render('register/index.html.twig',[
            'form' => $form->createView()
        ]);
    }      
}