<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\TrickType;
use App\Form\CommentType;
use App\Repository\CategoryRepository;
use App\Repository\TrickRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    #[Route('/', name: 'trick_home')]
    public function home(): Response
    {
        return $this->render('trick/home.html.twig');
    }

    #[Route('/tricks', name: 'trick_showAll')]
    public function showAll(TrickRepository $trickRepository, Request $request): Response
    {
        // On va chercher le numéro de page dans l'url :
        $page = $request->query->getInt('page', 1); // $request->query : va chercher dans l'url, s'il ne trouve pas 'page on considère que c'est pa gae 1. Genre: https://127.0.0.1:8000/tricks?page=2

        // On va chercher la liste des tricks :
        // $tricks = $trickRepository->findAll();
        $tricks = $trickRepository->findTricksPaginated($page, 2); // numéro de la page, limite

        return $this->render('trick/showAll.html.twig', [
            'tricks' => $tricks,
            'page' => $page,
        ]);
    }

    #[Route('/trick/creer', name: 'trick_new')]
    public function newTrick(Request $request, EntityManagerInterface $em): Response
    {
        // make sure the user is authenticated first,
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $form->getData();
            $trick->setCreatedDate(new \DateTime());
            $trick->setUpdatedDate(new \DateTime());
            $trick->setAuthor($this->getUser());
            $trick->setSlug($this->slugger->slug($trick->getName()));
            $em->persist($trick);
            $em->flush();

            $this->addFlash('success', "Votre figure a été enregistrée");

            return $this->redirectToRoute('trick_showAll');
        }

        return $this->renderForm('trick/new.html.twig', [
            'formTrick' => $form
        ]);
    }

    #[Route('/trick/{slug}', name: 'trick_showOne')]
    public function showOne(Trick $trick, CommentRepository $commentRepository, Request $request, EntityManagerInterface $em): Response
    {

        // make sure the user is authenticated first, Non tous les visiteurs ont accès.
        // $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $comments = $commentRepository->findBy(['trick' => $trick->getId()], ['created_date' => 'DESC']);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setCreatedDate(new \DateTime());
            $comment->setTrick($trick);
            $comment->setUser($this->getUser());
            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', "Votre commentaire a été enregistré");

            return $this->redirectToRoute('trick_showOne', array('slug' => $trick->getSlug()));
        }

        return $this->renderForm('trick/showOne.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
            'formComment' => $form
        ]);
    }

    #[Route('/tricks/catégorie/{categorySlug}', name: 'trick_showByCategory')]
    public function showByCategory($categorySlug, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy(['slug' => $categorySlug]);
        $tricks = $category->getTrick();

        return $this->render('trick/showByCategory.html.twig', [
            'tricks' => $tricks,
        ]);
    }
}
