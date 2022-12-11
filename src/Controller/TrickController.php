<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\TrickRepository;
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
    public function index(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->findAll();
        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks,
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
            return $this->redirectToRoute('trick_showOne', ['slug' => $trick->getSlug()]);
        }

        return $this->renderForm('trick/new.html.twig', [
            'formTrick' => $form
        ]);
    }

    #[Route('/trick/{slug}', name: 'trick_showOne')]
    public function showOne(Trick $trick): Response
    {
        return $this->render('trick/showOne.html.twig', [
            'trick' => $trick,
        ]);
    }
}
