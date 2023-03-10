<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExceptionController extends AbstractController
{
    #[Route('/erreur404', name: 'error404')]
    public function showException()
    {
        return $this->render('bundles/TwigBundle/Exceptions/error404.html.twig');
    }
}

// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\ErrorHandler\Exception\FlattenException;
// use Symfony\Component\HttpFoundation\Response;

// class ExceptionController extends AbstractController
// {
//     public function showException(FlattenException $exception): Response
//     {
//         // Log the exception
//         $this->get('logger')->error($exception->getMessage());

//         // Render the appropriate template
//         if ($exception->getStatusCode() === 404) {
//             return $this->render('bundles/TwigBundle/Exception/error404.html.twig', ['exception' => $exception]);
//         } else {
//             return $this->render('bundles/TwigBundle/Exception/error.html.twig', ['exception' => $exception]);
//         }
//     }
// }
