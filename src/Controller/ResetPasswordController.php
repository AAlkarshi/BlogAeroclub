<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use App\Service\TokenService; // Un service pour gérer les tokens
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mime\Email;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\ResetPasswordFormType;
use Symfony\Component\Form\FormFactoryInterface; // Import correct



#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    private $formFactory;
    private $tokenService;
    use ResetPasswordControllerTrait;
   

    public function __construct( TokenService $tokenService, FormFactoryInterface $formFactory,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {
        $this->tokenService = $tokenService;
    }





    /**
     * Display & process form to request a password reset.
     */
    #[Route('/forgot-password', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    { 
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer,
                $translator
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);

        
/*
        // Création du formulaire pour entrer l'email
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->add('submit', SubmitType::class, ['label' => 'Envoyer le lien de réinitialisation'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = $data['email'];

            // Recherche de l'utilisateur par email
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                // Génération du lien de réinitialisation (sans token)
                $resetLink = $this->generateUrl('app_reset_password', [
                    'email' => $email,
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                 // Envoi de l'email de réinitialisation
                 $emailMessage = (new Email())
                 ->from('noreply@example.com')
                 ->to($email)
                 ->subject('Réinitialisation de votre mot de passe')
                 ->html(sprintf('Cliquez sur ce lien pour réinitialiser votre mot de passe : <a href="%s">%s</a>', $resetLink, $resetLink));

             $mailer->send($emailMessage);

             $this->addFlash('success', 'Un email de réinitialisation de mot de passe a été envoyé.');

             return $this->redirectToRoute('app_forgot_password');
         } else {
             $this->addFlash('error', 'Aucun utilisateur trouvé avec cet email.');
         }
     }

     return $this->render('reset_password/request.html.twig', [
         'requestForm' => $form->createView(),
     ]); 
     */
 }










    /**
     * Confirmation page after a user has requested a password reset.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }


        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }








    /**
     * Valide et traite l'URL de réinitialisation que l'utilisateur a cliquée dans son e-mail.
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, string $token = null): Response
    {  
        // Si un token est présent dans l'URL
        if ($token) {
            $this->storeTokenInSession($token);
            // Récupère le token depuis la session
           # $token = $this->getTokenFromSession();

           // On stocke le token dans la session si ce n'est pas déjà fait
        if (!$this->getTokenFromSession()) {
            // On stocke le token dans la session et on le retire de l'URL
            // pour éviter que le token ne soit exposé à du JavaScript tiers.
            #$this->storeTokenInSession($token);
             
            return $this->redirectToRoute('app_reset_password', ['token' => $token]);
        }
    }

        // Récupère le token depuis la session si aucun token n'est dans l'URL
        $token = $this->getTokenFromSession();
        if (null === $token) {
             // Lève une exception si aucun token n'est trouvé ni dans l'URL ni dans la session
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        /*
         // Vérifiez si le token est valide
         if (!$this->tokenService->isValid($token)) {
            // Redirigez vers une page d'erreur ou affichez un message
            return $this->render('reset_password/error.html.twig', [
                'message' => 'Token invalide ou expiré.'
            ]);
        }
        */

            
        var_dump($token);

        
        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }
       

        // Le token est valide, on permet à l'utilisateur de changer son mot de passe
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);
            

            //AJOUT DE MOI
            $user = $this->getUser();

            //Encode le mdp
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            // Met à jour le mot de passe de l'utilisateur dans la base de données
            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            // Nettoie la session après la réinitialisation du mot de passe
            $this->cleanSessionAfterReset();

            // Redirige vers la page de connexion
            return $this->redirectToRoute('app_login');
        }
        
 
        // Affiche le formulaire de réinitialisation du mot de passe
        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
          'token' => $token,
        ]);
        
        
    }















    
    private function storeTokenInSession(string $token): void
    {
        // Stocke le token dans la session
        $this->get('session')->set('reset_password_token', $token);
    }

    private function getTokenFromSession(): ?string
    {
        // Récupère le token depuis la session
        return $this->get('session')->get('reset_password_token');
    }
















    #[Route('/process', name: 'process_reset_password')]
    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     '%s - %s',
            //     $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
            //     $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            // ));

            return $this->redirectToRoute('app_check_email');
        }
        

        $email = (new TemplatedEmail())
            ->from(new Address('blog@aeroclub.com', 'Admin du blog Aeroclub'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $mailer->send($email);
        

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);
        return $this->redirectToRoute('app_check_email');
    }



 



   
}


