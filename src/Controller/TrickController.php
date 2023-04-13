<?php

namespace App\Controller;

use App\Entity\Image;
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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $tricksPagination = $trickRepository->findTricksPaginated($currentPage, 3); // limit = 3

        return $this->render('trick/show_all.html.twig', [
            'tricksPagination' => $tricksPagination,
            'currentPage' => $currentPage,
        ]);
    }

    #[Route('/trick/creer', name: 'trick_new')]
    public function newTrick(Request $request, EntityManagerInterface $em): Response
    {
        // D'abord vérifier que l'utilisateur est authentifié
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

            // On récupère les images saisies dans le formulaire :
            $images = $form->get('image')->getData();

            foreach ($images as $image) {
                // On renome de fichier image qu'on stocke en webp :
                $imageFileName = md5(uniqid()) . '.webp';
                // On copie physiquement le fichier dans le dossier uploads :
                $image->move($this->getParameter('images_directory'), $imageFileName);
                // On stocke le nom de l'image dans la BDD : On va créer une nouvelle instance de l'entité image dans laquelle on faire un setName() puis on va ajouter cette instance dans l'entité Trick.
                $img = new Image();
                $img->setName($imageFileName);
                $trick->addImage($img);
            }

            $em->persist($trick);
            $em->flush();

            $this->addFlash('success', "Votre figure a été enregistrée");

            return $this->redirectToRoute('trick_showAll');
        }

        return $this->render('trick/new.html.twig', [
            'formTrick' => $form,
        ]);
    }

    #[Route('/trick/{trickSlug}', name: 'trick_showOne')]
    public function showOne(TrickRepository $trickRepository, CommentRepository $commentRepository, Request $request, EntityManagerInterface $em, $trickSlug): Response
    {
        // Récupération du trick associé au slug :
        $trick = $trickRepository->findOneBy(['slug' => $trickSlug]);
        if(!$trick){
            throw new NotFoundHttpException("Pas de Trick trouvé"); // c'est pas redondant avec "Page non trouvée"?
        }

        // Formulaire du commentaire :
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

        return $this->render('trick/show_one.html.twig', [
            'trick' => $trick,
            'trickSlug' => $trickSlug,
            'formComment' => $form,
        ]);
    }

    #[Route('/trick/{slug}/modifier', name: 'trick_edit')]
    public function editTrick(Trick $trick, Request $request, EntityManagerInterface $em): Response
    {
        // D'abord vérifier que l'utilisateur est authentifié
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // Et s'assurer que l'utilisateur est l'auteur :
        $this->denyAccessUnlessGranted("TRICK_EDIT", $trick); // ok

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les images saisies dans le formulaire :
            $images = $form->get('image')->getData();

            foreach ($images as $image) {
                // On renome de fichier image qu'on stocke en webp :
                $imageFileName = md5(uniqid()) . '.webp';
                // On copie physiquement le fichier dans le dossier uploads :
                $image->move($this->getParameter('images_directory'), $imageFileName);
                // On stocke le nom de l'image dans la BDD : On va créer une nouvelle instance de l'entité image dans laquelle on faire un setName() puis on va ajouter cette instance dans l'entité Trick.
                $img = new Image();
                $img->setName($imageFileName);
                $trick->addImage($img);
            }

            $trick = $form->getData();
            $trick->setUpdatedDate(new \DateTime());
            // $trick->setCategory() Il n'y a pas de setCategory
            $em->persist($trick);
            $em->flush();
            return $this->redirectToRoute('trick_showAll');
        }

        return $this->render('trick/edit.html.twig', [
            'trick' => $trick,
            'formTrick' => $form,
        ]);
    }

    #[Route('/trick/{slug}/supprimer', name: 'trick_delete')]
    public function deleteTrick(Trick $trick, EntityManagerInterface $em): Response
    {
        // D'abord vérifier que l'utilisateur est authentifié
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // Et s'assurer que l'utilisateur est l'auteur :
        $this->denyAccessUnlessGranted("TRICK_DELETE", $trick);

        // On supprime physiquement les images :
        $images = $trick->getImages();
        if($images){
            foreach ($images as $image) {
                // on récupère le chemin :
                $nomImage = $this->getParameter("images_directory") . '/' . $image->getName();
                // on vérifie si l'image existe :
                if(file_exists($nomImage)){ // return true
                    // on la supprime :
                    unlink($nomImage);
                }
            }
        }
        $em->remove($trick);
        $em->flush();

        return $this->redirectToRoute('trick_showAll');
    }


    #[Route('/trick/{trickSlug}/commentaires', name: 'trick_getComments', methods: ["GET"])]
    // exemple: /trick/TestNom1/commentaires?page=3
    public function getComments(CommentRepository $commentRepository, Request $request, $trickSlug)
    {
        // On va chercher le numéro de page dans l'url :
        $currentPage = $request->query->getInt('page', 1);

        // On va chercher le tableau (liste des commentaires + total de pages + limit) :
        $commentsPagination = $commentRepository->findCommentsPaginated($trickSlug, $currentPage, 10); // limit est imposé à 10 par OC
        // dd($commentsPagination);

        return $this->json($commentsPagination, 200, [], ['groups' => 'comment:read']);
        // $this->json() utilise SerializerInterface
    }

    #[Route('/tricks/catégorie/{categorySlug}', name: 'trick_showByCategory')]
    public function showByCategory(TrickRepository $trickRepository, Request $request, $categorySlug): Response
    {
        // On va chercher le numéro de page dans l'url :
        $currentPage = $request->query->getInt('page', 1);

        // On va chercher le tableau (liste des tricks par catégorie + total de pages +limit) :
        $tricksPagination = $trickRepository->findTricksByCategoryPaginated($categorySlug, $currentPage, 3); // limit = 3

        return $this->render('trick/show_by_category.html.twig', [
            'tricksPagination' => $tricksPagination,
            'currentPage' => $currentPage,
            'categorySlug' => $categorySlug,
        ]);
    }

    #[Route('/tricks/suppression_image/{id}', name: 'trick_delete_image', methods: ['DELETE'])]
    public function deleteImage(Image $image, Request $request, EntityManagerInterface $em): JsonResponse
    {
        // On récupère le contenu de la requête json sous forme de tableau
        $data = json_decode($request->getContent(), true);

        // On vérifie si le jeton CSRF contenu dans le tableau associatif est valide en utilisant la méthode isCsrfTokenValid().
        // Lorsque le client envoie une requête au serveur, il doit inclure le jeton CSRF dans la requête pour prouver qu'il est bien un utilisateur autorisé et non un attaquant.
        // isCsrfTokenValid() prend 2 paramètres : la clé utilisée pour stocker le jeton CSRF dans la session du client, qui est générée en concaténant la chaîne "delete" avec l'ID de l'image à supprimer, et la valeur du jeton CSRF qui a été envoyée avec la requête HTTP.
        // isCsrfTokenValid() compare la valeur du jeton CSRF stockée dans la session avec la valeur envoyée dans la requête AJAX pour vérifier que l'utilisateur qui effectue la demande est autorisé à le faire. Icis, la clé utilisée pour stocker le jeton CSRF dans la session est générée en concaténant la chaîne "delete" avec l'ID de l'image à supprimer.
        if($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])){
            // Si le token csrf est valide:
            // On supprimer le fichier de l'image
            $imagePath = $this->getParameter('images_directory') . '/' . $image->getName();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            // On supprime l'image de la base de données
            $em->remove($image);
            $em->flush();

            return new JsonResponse(['success' => true], 200);
            
            // La suppression a échoué
            return new JsonResponse(['error' => 'Erreur de suppression'], 400);
        }

        return new JsonResponse(['error' => 'Token invalide'], 400);
    }
}
