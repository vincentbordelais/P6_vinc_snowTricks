<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[Route('/commentaires/{comment_id}/{trick_slug}/supprimer', name: 'comment_delete')]
    public function deleteComment(CommentRepository $commentRepository, $comment_id, EntityManagerInterface $em, $trick_slug): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $comment = $commentRepository->find($comment_id);
        $em->remove($comment);
        $em->flush();

        return $this->redirectToRoute('trick_showOne', ['trickSlug' => $trick_slug]);
    }
}
