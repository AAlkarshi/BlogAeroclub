<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;


class UserController extends AbstractController
{

    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('newuser/inscription.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }


    /**
     * @Route("/user/new", name="user_new")
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérez l'email de l'entité User
            $email = $user->getEmail();

            // Persist et flush l'utilisateur
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_success');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }





#[Route('/comptedelete', name: 'app_user_delete')]
public function delete(Request $request): Response
{
    $user = $this->getUser(); 

     // Vérifier si l'utilisateur est connecté
     if(!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé');
    }

    // Récupérer le gestionnaire d'entités
    $entityManager = $this->getDoctrine()->getManager();
    
    // Supprimer l'utilisateur de la base de données
    $entityManager->remove($user);
    $entityManager->flush();
    
    // Déconnecter l'utilisateur
    $this->get('security.token_storage')->setToken(null);

    // Rediriger vers une page de confirmation ou une page d'accueil
    return $this->redirectToRoute('Accueil');
}


}
