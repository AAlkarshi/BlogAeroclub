<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\Text;
use App\Entity\Categorie;
use Symfony\Component\Security\Core\Security;
use App\Repository\PostRepository;

class ArticleController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        # gérer l'authentification et les autorisations users avec new va recup l'user Co
        $this->security = $security;
    }

    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/articleliste', name: 'app_liste_article')]
    public function list(ArticleRepository $articleRepository, CategorieRepository $categorieRepository): Response
    {
        $articles = $articleRepository->findAll();
        #pr afficher listes des categories
        $categories = $categorieRepository->findAll();

        return $this->render('article/list.html.twig', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }


    #[Route('/articleajout', name: 'app_ajout_article')]
    public function new(Request $request,CategorieRepository $categorieRepository, EntityManagerInterface $entityManager , Security $security): Response
    {
        // Récupérer l'user connecté
        $user = $security->getUser();

        // Récupérer tt les catégories depuis BDD
        $categories = $this->getDoctrine()->getRepository(Categorie::class)->findAll();

         // Formatter les catégories en un tableau associatif d'objet Categorie => nom
        $categoryChoix = [];
        foreach ($categories as $category) {
            $categoryChoix[$category->getName()] = $category;
        }

        $article = new Article();
        $article->setCreationDate(new \DateTime());
        $article->setUser($user);
        
        //Créer formulaire
        $form = $this->createForm(ArticleType::class, $article, ['categorie' => $categoryChoix]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            // Ajouter le message après avoir persisté et flushé l'article
            $this->addFlash('success', 'Article ajouté avec succès!');

            return $this->redirectToRoute('app_creer_un_post');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories,
        ]);
    }






//REVOIR LE BOUTON SUPPRESSION BUG APRES LE CLIC SUR LE BOUTON
#[Route('/articlesuppression/{id}', name: 'app_suppression_article')]
public function delete(Request $request, EntityManagerInterface $entityManager , $id): Response
{
    // Trouver la catégorie par ID
    $article = $entityManager->getRepository(Article::class)->find($id);

    if (!$article) {
        throw $this->createNotFoundException('Article non trouvé');
    }
    
    // Vérifier que l'utilisateur connecté est le créateur de la catégorie
    $user = $this->getUser();
    if ($article->getUser() !== $user) {
        throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette catégorie');
    }

        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('app_mes_articles'); 

}

#[Route('/articlemodification/{id}', name: 'app_modification_article')]
public function edit(Request $request,Article $article, CategorieRepository $categorieRepository, EntityManagerInterface $entityManager, $id): Response
{
    // Trouver l'article par ID
    $article = $entityManager->getRepository(Article::class)->find($id);
    $categories = $categorieRepository->findAll();
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if (!$article) {
        throw $this->createNotFoundException('Article non trouvé');
    }

    // Vérifier que l'utilisateur connecté est le créateur de l'article
    $user = $this->getUser();
    if ($article->getUser() !== $user) {
        throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cet article');
    }

    if ($form->isSubmitted() && $form->isValid()) {
        // Mettre à jour la date de modification
        $article->setCreationDate(new \DateTime());

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('app_mes_articles');
    }

    return $this->render('article/edit.html.twig', [
        'article' => $article,
        'form' => $form->createView(),
        'categories' => $categories,
    ]);
}



























    //AFFICHE LES CATEGORIES MAIS montre qu'1 catégorie -> se réfere au ID de table CATEGORIE
   #[Route('/MesArticles', name: 'app_mes_articles')]
   public function mesarticles(EntityManagerInterface $entityManager , CategorieRepository $categorieRepository): Response
   {
        $categories = $categorieRepository->findAll();
       // Récupérer l'user connecté
       $user = $this->security->getUser();
       // Récupérer toutes les articles créées par un mm USER
       $articles = $entityManager->getRepository(Article::class)->findBy(['user' => $user]);

       return $this->render('article/mesarticles.html.twig', [
           'articles' => $articles,
           'categories' => $categories,
       ]);
   }

   #ARTICLE PAR CATEGORIE
   #[Route('/articles-par-categorie/{id}', name: 'app_articles_par_categorie')]
   public function articlesParCategorie($id, ArticleRepository $articleRepository,CategorieRepository $categorieRepository, PostRepository $postRepository): Response
   {
       // Trouver la catégorie par ID
       $categorieparID = $categorieRepository->find($id);
       if (!$categorieparID) {
           throw $this->createNotFoundException('Catégorie non trouvée');
       }
       
       // Récupérer les articles appartenant à cette catégorie
       $articles = $articleRepository->findBy(['categorie' => $categorieparID]);
       // Vérifier s'il y a des articles dans cette catégorie
       if (empty($articles)) {
           return $this->redirectToRoute('app_articles_par_categorie_vide', ['id' => $id]);
       }
           
       // Récupérer les posts associés aux articles
       $firstPostByArticle = [];
       foreach ($articles as $article) {
           $posts = $postRepository->findBy(['article' => $article]);
           if (!empty($posts)) {
               $firstPostByArticle[$article->getId()] = $posts[0]; // Récupérer le premier post
           } else {
               $firstPostByArticle[$article->getId()] = null; // Pas de post associé
           }
       }
   
       // Récupérer toutes les catégories pour affichage
       $categories = $categorieRepository->findAll();
   
       return $this->render('article/articles_par_categorie.html.twig', [
           'articles' => $articles,
           'categorie' => $categorieparID,
           'categories' => $categories,
           'firstPostByArticle' => $firstPostByArticle, 
       ]);
   }
   













   #[Route('/articles-par-categorie-vide/{id}', name: 'app_articles_par_categorie_vide')]
   public function articlesParCategorieVide($id, CategorieRepository $categorieRepository): Response
   {    
       $categories = $categorieRepository->findAll();
       // Trouver la catégorie par ID
       $categorieparID = $categorieRepository->find($id);
   
       // Vérifier que la catégorie existe
       if (!$categorieparID) {
           throw $this->createNotFoundException('Catégorie non trouvée');
       }
   
       return $this->render('article/articles_par_categorie_vide.html.twig', [
           'categorieparID' => $categorieparID,
           'categories' => $categories,
       ]);
   }
   


}
