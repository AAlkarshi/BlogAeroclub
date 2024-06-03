<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Form\FormLancerUnPostType;
use App\Form\CategoryType;
use App\Entity\Categorie;
use App\Entity\Post;
use App\Entity\Article;
use Symfony\Component\Security\Core\Security;

class LancerUnPostController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    #[Route('/lancerunpost', name: 'app_lancer_un_post')]
    public function index(): Response
    {
        return $this->render('lancer_un_post/index.html.twig', [
            'controller_name' => 'LancerUnPostController',
        ]);
    }

    #[Route('/creationPost', name: 'app_creer_un_post')]
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(FormLancerUnPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                //getClientOriginalName permet de upload des fichiers
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                //slug permet de convertir en URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $post->setImage($newFilename);
            }
             // Récupérer l'utilisateur actuel
             $user = $this->security->getUser();
             $post->setUser($user);  

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('afficher_les_posts');
        }
        return $this->render('lancer_un_post/newpost.html.twig', [
            'form' => $form->createView(),
        ]);
    }



//LISTES DES POSTS
    #[Route('/afficherLesPosts', name: 'afficher_les_posts')]
    public function list(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $posts = $entityManager->getRepository(Post::class)->findAll();
        
        // Tableau avec détails de chaque post
        $postDetails = [];
        foreach ($posts as $post) {
            $article = $post->getArticle(); 
            $categorie = $article->getCategorie(); // récup categorie associé au post

            $postDetails[] = [
                'title' => $article->getTitle(),
                'username' => $post->getUser()->getUsername(),
                'creationDate' => $article->getCreationDate(),
                'name' => $categorie->getName(),
                'content' => $post->getContent(),
                'image' => $post->getImage(), // depuis $post pour obtenir l'image
            ];
        }
    
        return $this->render('lancer_un_post/list.html.twig', [
            'postDetails' => $postDetails,
        ]);
    }
    
    






//AFFICHER UN POST EN PARTICULIER
    #[Route('/affichePost/{id}', name: 'app_affiche_un_post')]
    public function showpost(Request $request, SluggerInterface $slugger): Response
    {
        // Récupérer l'utilisateur actuel
        $user = $this->security->getUser();  
        $entityManager = $this->getDoctrine()->getManager();
        $articles = $entityManager->getRepository(Article::class)->findAll();
        $posts = $entityManager->getRepository(Post::class)->findAll();
      
         $postDetails = [];
         foreach ($posts as $post) {
             $article = $post->getArticle(); // récup l'article associé au post
             $categorie = $article->getCategorie(); 
 
             $postDetails[] = [
                 'title' => $article->getTitle(),
                 'username' => $post->getUser()->getUsername(),
                 'creationDate' => $article->getCreationDate(),
                 'name' => $categorie->getName(),
                 'content' => $post->getContent(),
                 'image' => $post->getImage(),
             ];
         }
       
       # return $this->redirectToRoute('afficher_les_posts');
        return $this->render('lancer_un_post/showpost.html.twig', [
            'postDetails' => $postDetails,
        ]);      
    }




































    /**
     * @Route("/category/success", name="category_success")
     */
    public function success(): Response
    {
        return new Response('Post à été créer avec succès !');
    }

}
