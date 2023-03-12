<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Annotation\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
class CategoryController extends AbstractController
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    #[Route('/categorie', name: 'category_showAll')]
    public function showAll(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render('category/show_all.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/categorie/creer', name: 'category_new')]
    public function newCategory(Request $request, EntityManagerInterface $em): Response
    {

        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $category->setSlug($this->slugger->slug($category->getName()));
            // $category->setTrick(); Il n'y a pas de setTrick(), 
            $em->persist($category);
            $em->flush();

            $this->addFlash('success', "Votre figure a été enregistrée");

            return $this->redirectToRoute('category_showAll');
        }

        return $this->renderForm('category/new.html.twig', [
            'formCategory' => $form
        ]);
    }

    #[Route('/categorie/{slug}', name: 'category_showOne')]
    public function showOne(Category $category): Response
    {
        return $this->render('category/show_one.html.twig', [
            'category' => $category
        ]);
    }

    #[Route('/categorie/{slug}/modifier', name: 'category_edit')]
    public function editCategory(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $category->setSlug($this->slugger->slug($category->getName()));
            // $category->setTrick(); Il n'y a pas de setTrick(), 
            $em->persist($category);
            $em->flush();
            return $this->redirectToRoute('category_showAll');
        }

        return $this->renderForm('category/edit.html.twig', [
            'category' => $category,
            'formCategory' => $form,
        ]);
    }

    #[Route('/categorie/{slug}/supprimer', name: 'category_delete')]
    public function deleteCategory(Category $category, EntityManagerInterface $em): Response
    {
        $categoryName = $category->getName();
        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute('category_showAll');
    }
}
