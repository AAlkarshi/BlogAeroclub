<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Repository\CategorieRepository;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;

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

            // Définir le rôle pour l'utilisateur
            $roles = $user->getRoles();
            $roles[] = 'ROLE_MOD';
            $user->setRoles(array_unique($roles));

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





//LISTE DES UTILISATEURS
#[Route('/comptelisteUser', name: 'app_liste_comptesUser')]
public function listCompteUser(UserRepository $UserRepository,CategorieRepository $CategorieRepository): Response
{
    $users = $UserRepository->findAll();

     #pr afficher listes des categories
     $categories = $CategorieRepository->findAll();

    return $this->render('user/listecompteUser.html.twig', [
        'users' => $users,
        'categories' => $categories,
    ]);
}



#[Route('/listeArticle', name: 'app_liste_articlesUser')]
public function listeArticles(ArticleRepository $articleRepository, CategorieRepository $CategorieRepository): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $articles = $articleRepository->findAll();
    $categories = $CategorieRepository->findAll();

    return $this->render('user/liste_articlesUserAdmin.html.twig', [
        'articles' => $articles,
        'categories' => $categories,
    ]);
}


//ADMIN supp un article
#[Route('/Adminarticlesuppression/{id}', name: 'app_delete_article')]
public function deleteArticle(Article $article, EntityManagerInterface $entityManager , $id): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
   # $article = $articleRepository->findAll();

     // Vérifier que l'article existe
     if (!$article) {
         throw $this->createNotFoundException('Article non trouvé');
     }

    // Assurez-vous que l'utilisateur a la permission de supprimer cet article
    if ($this->isGranted('ROLE_ADMIN') || $this->getUser() === $article->getUser()) {
        $entityManager->remove($article);
        $entityManager->flush();

        #$this->addFlash('success', 'L\'article a été supprimé avec succès.');
    } /* else {
        $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cet article.');
    } */

    return $this->redirectToRoute('app_liste_articles');
}





}
