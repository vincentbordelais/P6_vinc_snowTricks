<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use DateTimeZone;
use App\Entity\User;
use App\Service\SendMailService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'registration_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SendMailService $mailService): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));
            $token = bin2hex(random_bytes(16));
            $user->setConfirmationToken($token);
            $now = new DateTime('now', new DateTimeZone('Europe/Paris'));
            $expirationDate = $now->add(new DateInterval('PT'. 2 .'M'));
            $user->setConfirmationTokenExpiresAt($expirationDate);
            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();

            $mailService->send(
                'no-reply@example.com', // from
                $user->getEmail(),  // to
                'Activation de votre compte sur le site SnowTricks', // subject
                'registration/activationEmail.html.twig',['user' => $user, 'token' => $token]);

            // Afficher le fuseau horaire actuel
            echo date_default_timezone_get();
            // Afficher l'heure actuelle
            echo date('Y-m-d H:i:s');

            return $this->redirectToRoute('registration_pre_activation', ['email' => $user->getEmail()]);
        }

        return $this->render('registration/inscription.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/inscription/pre_activation/{email}', name: 'registration_pre_activation')]  // page bienvenu
    public function checkEmail(string $email): Response
    {
        return $this->render('registration/firstTokenSent.html.twig', [
            'email' => $email
        ]);
    }

    #[Route('/inscription/activation/{token}', name: 'registration_activation')] // lien du 1ier mail
    public function activation(string $token, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            throw new NotFoundHttpException('Cet utilisateur n\'existe pas.');
        }

        $now = new DateTime('now', new DateTimeZone('Europe/Paris'));
        
        // Le token a expiré
        if ($user->getConfirmationTokenExpiresAt() < $now) {
            return $this->render('registration/newTokenSent.html.twig', [
                'user' => $user
            ]);
        }

        // Le token est toujours valide
        $user->setIsVerified(true);
        $user->setConfirmationToken(null);
        $user->setConfirmationTokenExpiresAt(null);
        $entityManager->flush();

        return $this->redirectToRoute('security_login');
    }

    #[Route('/inscription/reactivation/{email}', name: 'registration_reactivate')] // lien du 2e mail
    public function reactivation(string $email, UserRepository $userRepository, EntityManagerInterface $entityManager, SendMailService $mailService): Response
    {
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new NotFoundHttpException('Cet utilisateur n\'existe pas.');
        }

        if ($user->getIsVerified()) {
            throw new \Exception('Ce compte a déjà été activé.');
        }

        $now = new DateTime('now', new DateTimeZone('Europe/Paris'));
        
        // Le token a expiré
        if ($user->getConfirmationTokenExpiresAt() < $now) {
            $token = bin2hex(random_bytes(16)); // génère un nouveau token
            $user->setConfirmationToken($token);
            $expirationDate = $now->add(new DateInterval('PT'. 2 .'M'));
            $user->setConfirmationTokenExpiresAt($expirationDate);
            $entityManager->persist($user);
            $entityManager->flush();

            $mailService->send(
                'no-reply@example.com', // from
                $user->getEmail(),  // to
                'Activation de votre compte sur le site SnowTricks', // subject
                'registration/activationEmail.html.twig',['user' => $user, 'token' => $token]);

            return $this->redirectToRoute('registration_pre_activation', ['email' => $user->getEmail()]);
        }
        // Le token est toujours valide
        return $this->redirectToRoute('registration_pre_activation', ['email' => $user->getEmail()]);
    }
    
}