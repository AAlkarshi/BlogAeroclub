<?php

// src/Controller/FriendController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CategorieRepository;

class FriendController extends AbstractController
{
    #[Route('/friends', name: 'list_friends')]
    public function listFriends(CategorieRepository $categorieRepository): Response
    {   
        $categories = $categorieRepository->findAll();
        $user = $this->getUser();
        $friends = $user->getFriends();
        $receivedFriendRequests = $user->getReceivedFriendRequests();
        $sentFriendRequests = $user->getSentFriendRequests();
        return $this->render('friend/list.html.twig', [
            'friends' => $user->getFriends(),
            'categories' => $categories,
            'receivedFriendRequests' => $receivedFriendRequests,
            'sentFriendRequests' => $sentFriendRequests,
        ]);
    }

    #[Route('/add-friend/{id}', name: 'add_friend')]
    public function addFriend(User $friend, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

         // Vérifier si l'utilisateur essaie de s'ajouter lui-même
         if ($user === $friend) {
            $this->addFlash('error', 'Vous ne pouvez pas vous ajouter en ami.');
            return $this->redirectToRoute('list_friends');
        }

        // Vérifier si la demande d'ami existe déjà
        if (!$user->getSentFriendRequests()->contains($friend)) {
            $user->addSentFriendRequest($friend);
            $friend->addReceivedFriendRequest($user);

            $em->persist($user);
            $em->persist($friend);
            $em->flush();
        }
        return $this->redirectToRoute('list_friends');
    }

    #[Route('/remove-friend/{id}', name: 'remove_friend')]
    public function removeFriend(User $friend, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if ($user->getFriends()->contains($friend)) {
            $user->removeFriend($friend);
            $em->persist($user);
            $em->flush();
        }
        return $this->redirectToRoute('list_friends');
    }


    #[Route('/accept-friend-request/{id}', name: 'accept_friend_request')]
    public function acceptFriendRequest(User $requester, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

         // Vérifier si l'utilisateur essaie de s'ajouter lui-même
         if ($user === $requester) {
            $this->addFlash('error', 'Vous ne pouvez pas vous ajouter en ami.');
            return $this->redirectToRoute('list_friends');
        }

        // Vérifier si la demande existe et si l'utilisateur actuel est le destinataire de la demande
        if ($user->getReceivedFriendRequests()->contains($requester)) {
            $user->addFriend($requester);
            $requester->addFriend($user);

            $user->removeReceivedFriendRequest($requester);
            $requester->removeSentFriendRequest($user);

            $em->persist($user);
            $em->persist($requester);
            $em->flush();
        }

        return $this->redirectToRoute('app_envoyer_message', ['username' => $requester->getUsername()]);
    }

    #[Route('/reject-friend-request/{id}', name: 'reject_friend_request')]
    public function rejectFriendRequest(User $requester, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Vérifier si la demande existe et si l'utilisateur actuel est le destinataire de la demande
        if ($user->getReceivedFriendRequests()->contains($requester)) {
            $user->removeReceivedFriendRequest($requester);
            $requester->removeSentFriendRequest($user);

            $em->persist($user);
            $em->persist($requester);
            $em->flush();
        }
        return $this->redirectToRoute('app_envoyer_message', ['username' => $requester->getUsername()]);
    }




}





?>