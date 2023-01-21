<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\TrickType;
use App\Form\CommentType;
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
        $currentPage = $request->query->getInt('page', 1);

        // On va chercher le tableau (liste des tricks + total de pages + limit) :
        $tricksPagination = $trickRepository->findTricksPaginated($currentPage, 2); // limit = 2

        return $this->render('trick/showAll.html.twig', [
            'tricksPagination' => $tricksPagination,
            'currentPage' => $currentPage,
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

    #[Route('/trick/{trickSlug}', name: 'trick_showOne')]
    public function showOne(TrickRepository $trickRepository, CommentRepository $commentRepository, Request $request, EntityManagerInterface $em, $trickSlug): Response
    {
        // Récupération du trick associé au slug :
        $trick = $trickRepository->findOneBy(['slug' => $trickSlug]);

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

            return $this->redirectToRoute('trick_showOne', array('trickSlug' => $trick->getSlug()));
        }

        return $this->renderForm('trick/showOne.html.twig', [
            'trick' => $trick,
            'trickSlug' => $trickSlug,
            'formComment' => $form,
        ]);
    }

    #[Route('/trick/{trickSlug}/commentaires', name: 'trick_getComments', methods:["GET"])]
    // /trick/TestNom1/commentaires?page=3
    public function getComments(CommentRepository $commentRepository, Request $request)
    {
        // On va chercher le numéro de page dans l'url :
        $currentPage = $request->query->getInt('page', 1);

        // On va chercher le tableau (liste des commentaires + total de pages + limit) :
        $commentsPagination = $commentRepository->findCommentsPaginated($currentPage, 10); // limit est imposé à 10 par OC
        // dd($commentsPagination);

        return $this->json($commentsPagination, 200, [], ['groups' => 'comment:read']); // $this->json() utilise SerializerInterface
    }

    #[Route('/tricks/catégorie/{categorySlug}', name: 'trick_showByCategory')]
    public function showByCategory(TrickRepository $trickRepository, Request $request, $categorySlug): Response
    {
        // On va chercher le numéro de page dans l'url :
        $currentPage = $request->query->getInt('page', 1);

        // On va chercher le tableau (liste des tricks par catégorie + total de pages +limit) :
        $tricksPagination = $trickRepository->findTricksByCategoryPaginated($categorySlug, $currentPage, 2); // limit = 2

        return $this->render('trick/showByCategory.html.twig', [
            'tricksPagination' => $tricksPagination,
            'currentPage' => $currentPage,
            'categorySlug' => $categorySlug,
        ]);
    }
}
