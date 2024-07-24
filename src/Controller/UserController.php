<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\Post;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Repository\CategorieRepository;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\PostRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


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

        //traite la requête pour gérer les données soumise au form
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérez l'email de l'entité User
            $email = $user->getEmail();

            // Définir le rôle pour l'utilisateur
            $roles = $user->getRoles();
            $roles[] = 'ROLE_MOD';

            //MAJ et supp les doublons
            $user->setRoles(array_unique($roles));

           // obtient le gestionnaire d'entité
            $entityManager = $this->getDoctrine()->getManager();

            //prepare user à l'ajout ds la bdd
            $entityManager->persist($user);

            //execute l'ajout
            $entityManager->flush();

            return $this->redirectToRoute('user_success');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }





    #[Route('/comptedelete', name: 'app_user_delete')]
    public function delete(Request $request,MessageRepository $messageRepository, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, ArticleRepository $articleRepository, PostRepository $postRepository): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour supprimer un compte.');
        }

         // Supprimer les messages envoyés par l'utilisateur
         $sentMessages = $messageRepository->findBy(['expediteur' => $user]);
         foreach ($sentMessages as $message) {
             $entityManager->remove($message);
         }
 
         // Supprimer les messages reçus par l'utilisateur
         $receivedMessages = $messageRepository->findBy(['destinataire' => $user]);
         foreach ($receivedMessages as $message) {
             $entityManager->remove($message);
         }

      
        // Supprimer l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        // Déconnecter l'utilisateur
        $tokenStorage->setToken(null);

        // Rediriger vers une page de confirmation ou d'accueil
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







 // SUPPRIMER USER en tant ADMIN 
    #[Route('/Adminuserdelete/{id}', name: 'app_ADMIN_user_delete')]
    #[IsGranted("ROLE_ADMIN")]
    public function deleteuser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
           
            // Supprimez d'abord les articles de l'utilisateur
           $articles = $user->getArticles();
           foreach ($articles as $article) {
               $entityManager->remove($article);
           }
           
           $entityManager->flush();
           
           //Supp ensuite l'user
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_liste_comptesUser');
    }

    
// MON PROFIL
    #[Route('/monprofil', name: 'app_mon_profil')]
    public function monProfil(CategorieRepository $CategorieRepository): Response
    {
        $categories = $CategorieRepository->findAll();
        $user = $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        return $this->render('user/mon_profil.html.twig', [
            'user' => $user,
            'categories' => $categories,
        ]);
    }

    


    #[Route('/monprofil/modifie', name: 'app_modifie_username')]
public function modifieUsername(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository): Response
{
    $user = $this->getUser();

    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé');
    }

    if ($request->isMethod('POST')) {
        $newUsername = $request->request->get('username');

        // Vérifier l'unicité du nom d'utilisateur
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['username' => $newUsername]);
        if ($existingUser && $existingUser !== $user) {
            $this->addFlash('error', 'Ce nom d\'utilisateur est déjà pris.');
            return $this->redirectToRoute('app_modifie_username');
        }

        // Sauvegarder l'ancien nom d'utilisateur
        $oldUsername = $user->getUsername();

        // Mettre à jour le nom d'utilisateur de l'utilisateur
        $user->setUsername($newUsername);
        $entityManager->persist($user);
        $entityManager->flush();

        // Mettre à jour tous les posts de l'utilisateur avec le nouveau nom d'utilisateur
        $this->updateUserPosts($entityManager, $user, $oldUsername, $newUsername, $postRepository);

        $this->addFlash('success', 'Le nom d\'utilisateur a été modifié avec succès.');

        return $this->redirectToRoute('app_mon_profil');
    }

    return $this->render('user/mon_profil.html.twig', [
        'user' => $user,
    ]);


}


#[Route('/monprofil/modifieEmail', name: 'app_modifie_email')]
public function modifieEmailUser(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository): Response
{
    $user = $this->getUser();

    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé');
    }

    if ($request->isMethod('POST')) {
        $newEmail = $request->request->get('email');

        // Vérifier l'unicité du nom d'utilisateur
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $newEmail]);
        if ($existingUser && $existingUser !== $user) {
            $this->addFlash('error', 'Cette email est déjà pris.');
            return $this->redirectToRoute('app_modifie_email');
        }

        // Sauvegarder l'ancien nom d'utilisateur
        $oldEmail = $user->getEmail();

        // Mettre à jour le nom d'utilisateur de l'utilisateur
        $user->setEmail($newEmail);
        $entityManager->persist($user);
        $entityManager->flush();

        // Mettre à jour tous les posts de l'utilisateur avec le nouveau nom d'utilisateur
        $this->updateUserPosts($entityManager, $user, $oldEmail, $newEmail, $postRepository);

        $this->addFlash('success', 'Email a été modifié avec succès.');

        return $this->redirectToRoute('app_mon_profil');
    }

    return $this->render('user/mon_profil.html.twig', [
        'user' => $user,
    ]);


}



   




private function updateUserPosts(EntityManagerInterface $entityManager,User $user,string $oldUsername,string $deletedUsername,PostRepository $postRepository): void {
    // Récupérer tous les posts de l'utilisateur à partir du repository
    $posts = $postRepository->findBy(['user' => $user]);

    foreach ($posts as $post) {
        // MAJr le contenu du post avec le nouveau nom d'utilisateur
        $content = $post->getContent();
        $updatedContent = str_replace($oldUsername . ':', $deletedUsername . ':', $content);
        $post->setContent($updatedContent);
        $entityManager->persist($post);
    }

    $entityManager->flush();
}












































/*
//ADMIN liste article 
#[Route('/listeArticleUserdepuisAdmin', name: 'app_liste_articlesUserdepuisAdmin')]
public function listeArticlesUserdepuisAdmin(ArticleRepository $articleRepository, CategorieRepository $CategorieRepository): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $articles = $articleRepository->findAll();
    $categories = $CategorieRepository->findAll();

    return $this->render('user/liste_articlesUserAdmin.html.twig', [
        'articles' => $articles,
        'categories' => $categories,
    ]);
}


#[Route('/AdminarticlesuppressionUserdepuisAdmin/{id}', name: 'app_delete_articledepuisAdmin')]
public function deleteArticleDepuisAdmin($id, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    // Vérifier que l'article existe
    $article = $articleRepository->find($id);
    if (!$article) {
        throw $this->createNotFoundException('Article non trouvé');
    }

    // Assurez-vous que l'utilisateur a la permission de supprimer cet article
    if ($this->isGranted('ROLE_ADMIN') || $this->getUser() === $article->getUser()) {
        $entityManager->remove($article);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_user_articles', ['id' => $article->getUser()->getId()]);
}


// Détail Article Utilisateur depuis ADMIN
#[Route('/user/{id}/articles', name: 'app_user_articles')]
public function userArticles(User $user, ArticleRepository $articleRepository,CategorieRepository $CategorieRepository): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
    $articles = $articleRepository->findBy(['user' => $user]);
    $categories = $CategorieRepository->findAll();

    return $this->render('user/user_articles.html.twig', [
        'articles' => $articles,
        'user' => $user,
        'categories' => $categories,
    ]);


    A RAJOUTER ds listecompteUser.html.twig 
    <td style="padding: 10px; border: 1px solid #ddd;">
        <a href="{{ path('app_user_articles', {'id': user.id}) }}">  {{ user.username }}</a>
    </td>
}

*/



}



