<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Error404Controller extends AbstractController
{
    #[Route('/erreur404', name: 'error404')]
    public function showAction(): Response
    {
        $pageTitle = 'Page non trouvée';
        $errorMessage = 'La page que vous recherchez est introuvable. Veuillez vérifier l\'URL saisie.';

        return $this->render('errors/error404.html.twig', [
            'pageTitle' => $pageTitle,
            'errorMessage' => $errorMessage,
        ])->setStatusCode(Response::HTTP_NOT_FOUND);
    }
}
