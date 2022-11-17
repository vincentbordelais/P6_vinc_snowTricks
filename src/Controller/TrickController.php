<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{
    #[Route('/', name: 'trick_home')]
    public function home(): Response
    {
        return $this->render('trick/home.html.twig');
    }

    #[Route('/trick', name: 'app_trick')]
    public function index(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->findAll();
        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    #[Route('/trick/new', name: 'trick_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setCreatedDate(new \DateTime());
            $trick->setUpdatedDate(new \DateTime());
            $em->persist($trick);
            $em->flush();
            return $this->redirectToRoute('trick_showOne', ['id' => $trick->getId()]);
        }

        return $this->renderForm('trick/new.html.twig', [
            'formTrick' => $form,
        ]);
    }

    #[Route('/trick/{id}', name: 'trick_showOne')]
    public function showOne(Trick $trick): Response
    {
        return $this->render('trick/showOne.html.twig', [
            'trick' => $trick,
        ]);
    }
}
