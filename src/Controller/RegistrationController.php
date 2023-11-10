<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private SendMailService $mailService;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, SendMailService $mailService)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->mailService = $mailService;
    }

    #[Route('/inscription', name: 'registration_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            ));
            $token = bin2hex(random_bytes(16));
            $user->setLoginToken($token);
            $now = new DateTime('now', new DateTimeZone('Europe/Paris'));
            $expirationDate = $now->add(new DateInterval('PT' . 30 . 'S'));
            $user->setLoginTokenExpiresAt($expirationDate);
            $user->setRoles(['ROLE_USER']);

            // On récupère l'avatar saisie dans le formulaire:
            $avatarUrl = $form->get('avatar')->getData();
            if (!empty($avatarUrl) && strpos($avatarUrl, 'https://api.dicebear.com/6.x/adventurer-neutral/svg?seed=') !== false) {
                $user->setAvatar($avatarUrl);
            } else {
                $this->addFlash('warning', 'Veuillez donner un URL valide pour votre avatar.');
                return $this->redirectToRoute('registration_register');
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->mailService->send(
                'no-reply@snowtricks.fr', // from
                $user->getEmail(),  // to
                'SnowTricks - Activation de votre compte', // subject
                'registration/activation_email.html.twig',
                ['user' => $user, 'token' => $token]
            ); // context

            return $this->redirectToRoute('registration_pre_activation', ['email' => $user->getEmail()]);
        }

        return $this->render('registration/inscription.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/inscription/pre_activation/{email}', name: 'registration_pre_activation')] // page bienvenu
    public function checkEmail(string $email): Response
    {
        $this->addFlash('success', 'Un email d\'activation vient de vous être envoyé à l\'adresse "' . $email . '". Le lien expirera dans 3 heures.');
        return $this->redirectToRoute('trick_home');
    }

    #[Route('/inscription/activation/{token}', name: 'registration_activation')] // lien du 1ier mail
    public function activation(string $token): Response
    {
        $user = $this->userRepository->findOneBy(['login_token' => $token]);

        if (!$user) {
            throw new NotFoundHttpException('Cet utilisateur n\'existe pas.');
        }

        $now = new DateTime('now', new DateTimeZone('Europe/Paris'));
        
        // Le token a expiré
        if ($user->getLoginTokenExpiresAt()->format('Y-m-d\TH:i:s\Z') < $now->format('Y-m-d\TH:i:s\Z')) {
            $lien = $this->generateUrl('registration_reactivate', ['email' => $user->getEmail()]);
            $message = 'Le lien de confirmation que vous avez suivi a expiré. Veuillez renvoyer une demande d\'activation. <a href="' . $lien . '">Nouvelle demande d\'activation</a>';
            $this->addFlash('warning', $message);

            return $this->redirectToRoute('trick_home');
        }

        // Le token est toujours valide
        $user->setLoginIsVerified(true);
        $user->setLoginToken(null);
        $user->setLoginTokenExpiresAt(null);
        $this->entityManager->flush();

        $this->addFlash('success', 'Votre compte est activé, vous pouvez vous connecter');
        return $this->redirectToRoute('security_login');
    }

    #[Route('/inscription/reactivation/{email}', name: 'registration_reactivate')] // lien du 2e mail
    public function reactivation(string $email): Response
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new NotFoundHttpException('Cet utilisateur n\'existe pas.');
        }

        if ($user->getLoginIsVerified()) {
            throw new \Exception('Ce compte a déjà été activé.');
        }

        $now = new DateTime('now', new DateTimeZone('Europe/Paris'));
        
        // Le token a encore expiré
        if ($user->getLoginTokenExpiresAt()->format('Y-m-d\TH:i:s\Z') < $now->format('Y-m-d\TH:i:s\Z')) {
            $token = bin2hex(random_bytes(16)); // génère un nouveau token
            $user->setLoginToken($token);
            $expirationDate = $now->add(new DateInterval('PT' . 30 . 'S'));
            $user->setLoginTokenExpiresAt($expirationDate);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->mailService->send(
                'no-reply@snowtricks.fr', // from
                $user->getEmail(),  // to
                'SnowTricks - Activation de votre compte', // subject
                'registration/activation_email.html.twig',
                ['user' => $user, 'token' => $token]
            ); // context

            return $this->redirectToRoute('registration_pre_activation', ['email' => $user->getEmail()]);
        }
        // Le token est toujours valide
        $user->setLoginIsVerified(true);
        $user->setLoginToken(null);
        $user->setLoginTokenExpiresAt(null);
        $this->entityManager->flush();

        $this->addFlash('success', 'Votre compte est activé, vous pouvez vous connecter');
        return $this->redirectToRoute('security_login');
    }
}
