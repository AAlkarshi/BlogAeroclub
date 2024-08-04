<?php
// MessageController.php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class MessageController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/send-message', name: 'send_message', methods: ['POST'])]
    public function sendMessage(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté (expéditeur)
        $expediteur = $this->getUser();

         // Vérifier si l'utilisateur est connecté
         if (!$expediteur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour envoyer un message.');
            return $this->redirectToRoute('app_login');
        }

        $destinataireUsername = $request->request->get('destinataire_username');
        $content = $request->request->get('content');

        // Récupérer le destinataire à partir du nom d'utilisateur
        $destinataire = $entityManager->getRepository(User::class)->findOneBy(['username' => $destinataireUsername]);

        // Vérifier si le destinataire existe
        if (!$destinataire) {
            throw $this->createNotFoundException('Destinataire non trouvé');
        }

        // Empêcher l'utilisateur de s'envoyer un message à lui-même
        if ($expediteur === $destinataire) {
            $this->addFlash('error', 'Vous ne pouvez pas vous envoyer un message à vous-même.');
            return $this->redirectToRoute('app_envoyer_message', ['username' => $destinataireUsername]);
        }

        // Créer le message
        $message = new Message();
        $message->setExpediteur($expediteur);
        $message->setDestinataire($destinataire);
        $message->setContent($content);
        $message->setCreatedAt(new \DateTimeImmutable());

        // Enregistrer le message en base de données
        $entityManager->persist($message);
        $entityManager->flush();

        return new Response('Message envoyé avec succès', Response::HTTP_OK);
    }

    /* Affichage de la discussion privé */
    #[Route('/message/send/{username}', name: 'app_envoyer_message', methods: ['GET', 'POST'])]
    public function showSendMessageForm(string $username, UserRepository $userRepository, MessageRepository $messageRepository, CategorieRepository $categorieRepository, Request $request, PaginatorInterface $paginator): Response
    {

       

        $categories = $categorieRepository->findAll();
        /* security gestion des utilisateurs et de leurs sessions */
        $user = $this->security->getUser();
        $destinataire = $userRepository->findOneBy(['username' => $username]);
        
        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login'); 
        }

        // Vérifier si l'utilisateur essaie de s'envoyer un message à lui-même
        if ($user === $destinataire) {
            $this->addFlash('error', 'Vous ne pouvez pas vous envoyer un message à vous-même.');
            return $this->redirectToRoute('list_conversations');
        }
    
        // Vérifier si destinataire est un ami et contains vérifie si cet collection contient le destinataire
        $isFriend = $user->getFriends()->contains($destinataire);    
        // Vérifier si une demande d'ami a déjà été envoyée
        $hasSentFriendRequest = $user->getSentFriendRequests()->contains($destinataire);
        $hasReceivedFriendRequest = $user->getReceivedFriendRequests()->contains($destinataire);
    
        $queryBuilder = $messageRepository->createQueryBuilder('m')
            ->where('m.expediteur = :user AND m.destinataire = :destinataire')
            ->orWhere('m.expediteur = :destinataire AND m.destinataire = :user')
            ->setParameter('user', $user)
            ->setParameter('destinataire', $destinataire)
            ->orderBy('m.createdAt', 'ASC');
    
        /* PAGINATION */
        $pagination = $paginator->paginate(
            $queryBuilder, // Requête paginée
            $request->query->getInt('page', 1), // Numéro de page
            6 // Nbx d'éléments par page
        );
    
        if ($request->isMethod('POST')) {
            $content = $request->request->get('content');

            if ($user === $destinataire) {
                $this->addFlash('error', 'Vous ne pouvez pas vous envoyer un message à vous-même.');
                return $this->redirectToRoute('app_envoyer_message', ['username' => $username]);
            }
    
            // Créer le message
            $message = new Message();
            $message->setExpediteur($user);
            $message->setDestinataire($destinataire);
            $message->setContent($content);
            $message->setCreatedAt(new \DateTimeImmutable());
    
            // Enregistrer le message en base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();
    
            // Rediriger avec un message de succès
            return $this->redirectToRoute('app_envoyer_message', ['username' => $username, 'messageSent' => true]);
        }
    
        return $this->render('message/send.html.twig', [
            'destinataire' => $destinataire,
            'pagination' => $pagination,
            'categories' => $categories,
            'isFriend' => $isFriend,
            'hasSentFriendRequest' => $hasSentFriendRequest, 
            'hasReceivedFriendRequest' => $hasReceivedFriendRequest,
        ]);
    }
    



    
#[Route('/message/list-conversations', name: 'list_conversations')]
public function listConversations(MessageRepository $messageRepository, PaginatorInterface $paginator, CategorieRepository $categorieRepository, Request $request): Response
{
    $categories = $categorieRepository->findAll();
    $user = $this->getUser();

     // Récupérer les utilisateurs avec qui l'utilisateur a eu des conversations
     $conversations = $messageRepository->findUniqueConversationsForUser($user);

    return $this->render('message/listConvEntreUser.html.twig', [
        'conversations' => $conversations,
        'categories' => $categories,
    ]);
}


    





}

