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
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Mime\Email;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;



#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private SessionInterface $session;
    private $validator;
    private CategorieRepository $categorieRepository;

    public function __construct(ValidatorInterface $validator,EntityManagerInterface $entityManager, LoggerInterface $logger,CategorieRepository $categorieRepository,  SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->session = $session;
        $this->validator = $validator;
        $this->categorieRepository = $categorieRepository;
    }

    #[Route('/password/request', name: 'app_password_request')]
    public function request(ValidatorInterface $validator,Request $request,UserRepository $userRepository, SessionInterface $session,TokenGeneratorInterface $tokenGenerator, CategorieRepository $categorieRepository, MailerInterface $mailer): Response
    {
        $categories = $categorieRepository->findAll();
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
    
           /*   // Validation de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Invalid email address.');
            return $this->render('reset_password/request.html.twig');
        }*/

        $user = $userRepository->findOneBy(['email' => $email]);

        if ($user) {
            $token = $tokenGenerator->generateToken();
            $expiresAt = (new \DateTime())->modify('+1 hour'); // Token expires in 1 hour

            // Stocker le token et la date d'expiration dans la session
            $session->set('password_reset_token', [
                'token' => $token,
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'user_id' => $user->getId(),
            ]);

            $resetUrl = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            $emailMessage = (new Email())
                ->from('your_email@gmail.com')
                ->to($user->getEmail())
                ->subject('Password Reset Request')
                ->html('<p>To reset your password, please visit <a href="' . $resetUrl . '">this link</a></p>');

            $mailer->send($emailMessage);

            $this->addFlash('success', 'An email has been sent with instructions to reset your password.');
            return $this->redirectToRoute('app_check_email');
        }

    
            $this->addFlash('error', 'No user found with this email address.');
        }

        return $this->render('reset_password/request.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request,SessionInterface $session,string $token,UserRepository $userRepository,CategorieRepository $categorieRepository,EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $categories = $categorieRepository->findAll();
        
        $storedToken = $session->get('password_reset_token');
        $userId = $session->get('password_reset_user');

        if (!$storedToken || $storedToken['token'] !== $token || new \DateTime() > new \DateTime($storedToken['expires_at'])) {
            $this->addFlash('error', 'The token is invalid or has expired.');
            return $this->redirectToRoute('app_password_request');
        }
    
        $user = $userRepository->find($storedToken['user_id']);
    
        if (!$user) {
            $this->addFlash('error', 'No user found.');
            return $this->redirectToRoute('app_password_request');
        }
    
        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('password');
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
    
            $this->entityManager->persist($user);
            $this->entityManager->flush();
    
            // Clear session after successful password reset
            $session->remove('password_reset_token');
    
            $this->addFlash('success', 'Password successfully reset.');
            return $this->redirectToRoute('app_check_email');
        }

        return $this->render('reset_password/reset.html.twig', [
            'token' => $token,
            'categories' => $categories,
        ]);
    }


    
    #[Route('/process', name: 'process_reset_password')]
    public function processSendingPasswordResetEmail(CategorieRepository $categorieRepository,Request $request, MailerInterface $mailer): RedirectResponse
    {
        $categories = $categorieRepository->findAll();
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


    #[Route('/reset-password/check-email', name: 'app_check_email')]
    public function checkEmail(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findAll();

        return $this->render('reset_password/check_email.html.twig', [
            'categories' => $categories,
        ]);
    }



    
}
