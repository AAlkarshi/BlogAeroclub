<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieRepository;

use Symfony\Component\HttpFoundation\Request;

use App\Form\CategorieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

//Importer 2 classes
use App\Entity\Categorie;
use App\Entity\Article;
class CategorieController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    #[Route('/categorie', name: 'app_categorie')]
    public function index(): Response
    {
        return $this->render('categorie/index.html.twig', [
            'controller_name' => 'CategorieController',
        ]);
    }




    //LISTES DES CATEGORIES
    #[Route('/categorieliste', name: 'app_liste_categorie')]
    public function list(CategorieRepository $categorieRepository): Response
    {
        
        $categories = $categorieRepository->findAll();

        return $this->render('categorie/list.html.twig', [
            'categories' => $categories,
        ]);
    }







    #[Route('/categorieajout', name: 'app_ajout_categorie')]
    public function new(Request $request, EntityManagerInterface $entityManager, CategorieRepository $categorieRepository): Response
    {
         $categories = $categorieRepository->findAll();
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);

        //permet d'hydrater
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorie);
            $entityManager->flush();

            //REDIRECTION SE FAIT MAIS RIEN NE S'AFFICHE QD L AJOUT EST FAIT
            return $this->redirectToRoute('app_ajout_article'); 
        }

        return $this->render('categorie/new.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories,
        ]);
    }





    #[Route('/categoriesuppression/{id}', name: 'app_suppression_categorie')]
    public function delete(Request $request, EntityManagerInterface $entityManager , $id): Response
    {
        $categorie = $entityManager->getRepository(Categorie::class)->find($id);
        if (!$categorie) {
            throw $this->createNotFoundException('La catégorie n\'existe pas');
        }
        // Vérifier que l'utilisateur connecté est le créateur de la catégorie 
        $user = $this->getUser();
        $articles = $categorie->getHaves();
        
        // Vérifier que l'USER connecté est le créateur de la catégorie
        $userIsOwner = false;
        foreach ($articles as $article) {
            if ($article->getUser() === $user) {
                $userIsOwner = true;
                break;
            }
        }

        if (!$userIsOwner) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette catégorie');
        }
            $entityManager->remove($categorie);
            $entityManager->flush();
            //Redirection vers liste de catégorie
            return $this->redirectToRoute('app_mes_categories');  
    }








    #[Route('/categoriemodification/{id}', name: 'app_modification_categorie')]
    public function edit(Request $request, CategorieRepository $categorieRepository, EntityManagerInterface $entityManager, $id): Response
    {
        $categories = $categorieRepository->findAll();

        //Recupere la catégorie avec ID
        $categoriedeID = $entityManager->getRepository(Categorie::class)->find($id);
        if (!$categoriedeID) {
            throw $this->createNotFoundException('La catégorie n\'existe pas');
        }
    
        // Récupérer l'user connecté
        $user = $this->getUser();
    
        // Vérifier que l'user connecté a des articles dans cette catégorie
        $articles = $categoriedeID->getHaves();
        $userIsOwner = false;
        foreach ($articles as $article) {
            if ($article->getUser() === $user) {
                $userIsOwner = true;
                break;
            }
        }
    
        // Création et traitement du formulaire
        $form = $this->createForm(CategorieType::class, $categoriedeID);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            //flush demande au serveur de sortir
            $entityManager->flush();
    
            return $this->redirectToRoute('app_mes_categories');
        }
    
        return $this->render('categorie/edit.html.twig', [
            'categorie' => $categoriedeID,
            'form' => $form->createView(),
            'categories' => $categories,
        ]);
    }
    

































//MES CATEGORIES 
//AFFICHE LES CATEGORIES MAIS montre qu'1 catégorie -> se réfere au ID de table CATEGORIE
   #[Route('/MesCategories', name: 'app_mes_categories')]
   public function mescategories(EntityManagerInterface $entityManager): Response
   {  
       $user = $this->getUser();
       // Récupérer les catégories créées par un mm USER
       $articles = $entityManager->getRepository(Article::class)->findBy(['user' => $user]);
       $categories = [];
    
    foreach ($articles as $article) {
        //Entité article -> getCategorie permet de récupérer la catégorie issue de la table article
        $categorie = $article->getCategorie();

        //in_array cherche la 1er valeur ds un Tableau 2eme valeur
        if ($categorie && !in_array($categorie, $categories)) {
            $categories[] = $categorie;
        }
    }
       return $this->render('categorie/mescategories.html.twig', [
           'categories' => $categories,
       ]);
   }
   
}