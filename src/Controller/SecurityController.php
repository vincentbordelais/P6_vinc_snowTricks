<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'security_login')]
    public function login(AuthenticationUtils $authenticationUtils, UserRepository $userRepository): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        // retrouver le dernier identifiant de connexion utilisé
        $lastUsername = $authenticationUtils->getLastUsername();

        // si piratage (nécessaire ?):
        $user = $userRepository->findOneBy(['username' => $lastUsername]);
        if ($user && $user->getIsVerified() === false) {
            return $this->render('security/emailNotVerified.html.twig');
        }

        return $this->render('security/connexion.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in config/packages/security.yaml
     */
    #[Route('/deconnexion', name: 'security_logout')]
    public function logout(): void
    {
        throw new \Exception('Vous êtes déconnecté');
    }
}
