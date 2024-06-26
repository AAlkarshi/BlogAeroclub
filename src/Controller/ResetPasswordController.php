<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieRepository;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;



#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private SessionInterface $session;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger,  SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->session = $session;
    }

    #[Route('/forgot-password', name: 'app_forgot_password_request')]
    public function request(Request $request, CategorieRepository $categorieRepository, MailerInterface $mailer): Response
    {
        $categories = $categorieRepository->findAll();
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail($request, $mailer);
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
            'categories' => $categories,
        ]);
    }

    #[Route('/reset', name: 'app_reset_password')]
    public function reset(Request $request, CategorieRepository $categorieRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $categories = $categorieRepository->findAll();

        // Récupérez l'email depuis la session
        $email = $this->get('session')->get('reset_password_email');

        if (!$email) {
            throw $this->createNotFoundException('No email found in the session.');
        }

        // Recherchez l'utilisateur par email
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $this->addFlash('error', 'Aucun utilisateur trouvé avec cet e-mail.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            $this->addFlash('success', 'Mot de passe réinitialisé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
            'categories' => $categories,
        ]);
    }


    
    #[Route('/process', name: 'process_reset_password')]
    public function processSendingPasswordResetEmail(Request $request, MailerInterface $mailer): RedirectResponse
    {
        $emailFormData = $request->request->get('reset_password_request_form')['email'];
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $emailFormData]);

        if (!$user) {
            $this->addFlash('error', 'Aucun utilisateur trouvé avec cet e-mail.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        // Stockez l'email dans la session
        $this->get('session')->set('reset_password_email', $emailFormData);
        return $this->redirectToRoute('app_reset_password');
    }



    
}
