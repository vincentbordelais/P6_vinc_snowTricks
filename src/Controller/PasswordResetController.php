<?php

namespace App\Controller;

use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordResetController extends AbstractController
{
    private UserRepository $userRepository;
    private SendMailService $mailService;
    private EntityManagerInterface $entityManager;

    public function __construct(UserRepository $userRepository, SendMailService $mailService, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->mailService = $mailService;
        $this->entityManager = $entityManager;
    }
    
    #[Route('/oubli_du_mot_de_passe', name: 'forget_password')]
    public function forgetPassword(Request $request)
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userRepository->findOneByEmail($form->get('email')->getData());

            if ($user) {
                $token = bin2hex(random_bytes(16));
                $user->setPasswordToken($token);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                
                $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL); // génère un lien du type: https://127.0.0.1:8000/reset_du_mot_de_passe/84af392a0d6fa8a5ee73e23f262c6820

                $this->mailService->send(
                    'no-reply@snowtricks.fr', // from
                    $user->getEmail(),  // to
                    'SnowTricks - Réinitialiser votre mot de passe', // subject
                    'password_reset/reset_password_email.html.twig',
                    ['user' => $user, 'url' => $url]
                ); // context

                $this->addFlash('success', 'Un email vous a été envoyé avec les instructions pour réinitialiser votre mot de passe.');
                return $this->redirectToRoute('security_login');
            }
            $this->addFlash('danger', 'Un problème est survenu, veuillez vérifier la saisie de votre e-mail.');
            return $this->redirectToRoute('security_login');
        }

        return $this->render('password_reset/reset_password_request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reset_du_mot_de_passe/{token}', name: 'reset_password')]
    public function resetPassword($token, Request $request, UserPasswordHasherInterface $userPasswordHasher)
    {
        $user = $this->userRepository->findOneBy(['password_token' => $token]);
                
        if ($user) {
            $form = $this->createForm(ResetPasswordType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setPasswordToken(''); // efface le token
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été changé avec succès');
                return $this->redirectToRoute('security_login');
            }

            return $this->render('password_reset/reset_password.html.twig', [
                'form' => $form->createView()
            ]);
        }
        $this->addFlash('danger', 'Token invalide');
        return $this->redirectToRoute('security_login');
    }
}
