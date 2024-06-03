<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;


#AJOUT
use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\Text;
use App\Entity\Categorie;
use Symfony\Component\Security\Core\Security;

class ArticleController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
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
    public function list(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        return $this->render('article/list.html.twig', [
            'articles' => $articles,
        ]);
    }


    #[Route('/articleajout', name: 'app_ajout_article')]
    public function new(Request $request, EntityManagerInterface $entityManager , Security $security): Response
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

            #return $this->redirectToRoute('article_success'); // Vous devez définir une route pour la réussite
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }






//REVOIR LE BOUTON SUPPRESSION BUG APRES LE CLIC SUR LE BOUTON
#[Route('/articlesuppression/{id}', name: 'app_suppression_article')]
public function delete(Request $request, EntityManagerInterface $entityManager , $id): Response
{
    // Trouver la catégorie par ID
    $article = $entityManager->getRepository(Article::class)->find($id);

    // Vérifier que l'article existe
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

        //Redirection vers liste de catégorie
        return $this->redirectToRoute('app_mes_articles'); 

   /* return $this->render('article/suppression.html.twig', [
        'form' => $form->createView(),
        'article' => $article
    ]); */
}






#[Route('/articlemodification/{id}', name: 'app_modification_article')]
public function edit(Request $request, EntityManagerInterface $entityManager, $id): Response
{
    // Trouver l'article par ID
    $article = $entityManager->getRepository(Article::class)->find($id);

    // Vérifier que l'article existe
    if (!$article) {
        throw $this->createNotFoundException('Article non trouvé');
    }

    // Vérifier que l'utilisateur connecté est le créateur de l'article
    $user = $this->getUser();
    if ($article->getUser() !== $user) {
        throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cet article');
    }

    // Création et traitement du formulaire
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('app_mes_articles');
    }

    return $this->render('article/edit.html.twig', [
        'article' => $article,
        'form' => $form->createView(),
    ]);
}



























    //AFFICHE LES CATEGORIES MAIS montre qu'1 catégorie -> se réfere au ID de table CATEGORIE
   #[Route('/MesArticles', name: 'app_mes_articles')]
   public function mesarticles(EntityManagerInterface $entityManager): Response
   {
       // Récupérer l'user connecté
       $user = $this->security->getUser();

       // Récupérer toutes les articles créées par un mm USER
       $articles = $entityManager->getRepository(Article::class)->findBy(['user' => $user]);

       return $this->render('article/mesarticles.html.twig', [
           'articles' => $articles,
       ]);
   }







}
