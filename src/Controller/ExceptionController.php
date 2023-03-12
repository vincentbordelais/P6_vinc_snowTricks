<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExceptionController extends AbstractController
{
    #[Route('/erreur404', name: 'error404')]
    public function showException()
    {
        return $this->render('errors/error404.html.twig');
    }
}
