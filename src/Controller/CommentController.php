<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CommentController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commentaire', name: 'app_commentaire')]
    public function index( Request $request): Response
    {
        $commentaire = new Comment();
        $form = $this->createForm(CommentType::class, $commentaire);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire = $form->getData();
            $commentaire->setCreatedAt(new \DateTimeImmutable());
            $commentaire->setVerified('non');

            $this->entityManager->persist($commentaire);
            $this->entityManager->flush();
            return $this->redirectToRoute('app_home');
        } else {

            return $this->render('comment/index.html.twig', [
                'controller_name' => 'CommentaireController',
                'form' => $form->createView()
            ]);
        }
    }

    #[Route('/liste-commentaires', name: 'app_commentaire_liste')]
public function commentList( EntityManagerInterface $entityManager): Response
{
    $commentaires = $entityManager->getRepository(Comment::class)->findByVerified();

    return $this->render('comment/comments.html.twig', [
        'commentaires' => $commentaires,
    ]);
}

}