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

        $article = new Article();
        $article->setCreationDate(new \DateTime());
        $article->setUser($user);
        
        //Créer formulaire
        $form = $this->createForm(ArticleType::class, $article, ['categorie' => $categories]);
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


}
