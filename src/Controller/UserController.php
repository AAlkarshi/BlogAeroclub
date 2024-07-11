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
    public function delete(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, PostRepository $postRepository): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Marquer l'utilisateur comme supprimé (sans le supprimer réellement de la base de données)
        $oldUsername = $user->getUsername();
        $deletedUsername = $oldUsername . ' (Utilisateur supprimé)';
        $user->setUsername($deletedUsername);
        $entityManager->flush();

        // Mettre à jour tous les posts de l'utilisateur avec le nouveau nom d'utilisateur
        $this->updateUserPosts($entityManager, $oldUsername, $deletedUsername, $postRepository);

        // Déconnecter l'utilisateur
        $tokenStorage->setToken(null);

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



   




    private function updateUserPosts(EntityManagerInterface $entityManager, User $user, string $oldUsername, string $newUsername, PostRepository $postRepository): void
{
    // Récupérer tous les posts
    $posts = $postRepository->findAll();

    foreach ($posts as $post) {
        // Mettre à jour le contenu du post avec le nouveau nom d'utilisateur
        $content = $post->getContent();
        $updatedContent = str_replace($oldUsername . ':', $newUsername . ':', $content);
        $post->setContent($updatedContent);
        $entityManager->persist($post);
    }

    $entityManager->flush();
}



}



