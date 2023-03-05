<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'security_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // récupére l'erreur d'authentification, qui est stockée dans la session (s'il y en a une).
        $error = $authenticationUtils->getLastAuthenticationError();
        // Le message ($error) retourné est "Invalid credentials." pour un e-mail invalide ou un mot de passe incorrect. Je l'ai traduit par "Adresse e-mail invalide"

        // dernier username de connexion utilisé
        $lastUsername = $authenticationUtils->getLastUsername();

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
