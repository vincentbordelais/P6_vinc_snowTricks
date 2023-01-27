<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Require ROLE_AUTHOR for all the actions of this controller
 */
// #[IsGranted('ROLE_AUTHOR')]
class AdminController extends AbstractController
{
    #[Route('/trick/{slug}/modifier', name: 'trick_edit')]
    public function editTrick(Trick $trick, Request $request, EntityManagerInterface $em): Response
    {
        // make sure the user is authenticated first :
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // s'assurer que l'utilisateur est l'auteur :
        $this->denyAccessUnlessGranted("TRICK_EDIT", $trick); // ok

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $form->getData();
            $trick->setUpdatedDate(new \DateTime());
            // $trick->setCategory() Il n'y a pas de setCategory
            $em->persist($trick);
            $em->flush();
            return $this->redirectToRoute('trick_showAll');
        }

        return $this->renderForm('trick/edit.html.twig', [
            'trick' => $trick,
            'formTrick' => $form,
        ]);
    }

    #[Route('/trick/{slug}/supprimer', name: 'trick_delete')]
    public function deleteTrick(Trick $trick, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted("TRICK_DELETE", $trick);

        $trickName = $trick->getName();
        $em->remove($trick);
        $em->flush();

        return $this->redirectToRoute('trick_showAll');
    }

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
