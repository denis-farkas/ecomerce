<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\CommentRepository;

class SectionCommentController extends AbstractController
{

    public function index(CommentRepository $commentaireRepository): Response
    {
        return $this->render('comment/section-comments.html.twig', [
            'commentaires' => $commentaireRepository->findRandomVerifiedComments()
             ]
        );
    }
}