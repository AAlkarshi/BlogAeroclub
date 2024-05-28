<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

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
}
