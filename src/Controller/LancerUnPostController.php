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
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PostRepository;



use App\Repository\CategorieRepository;

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
    public function new(Request $request, SluggerInterface $slugger ,CategorieRepository $categorieRepository): Response
    {
        $post = new Post();
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(FormLancerUnPostType::class, $post);
        $form->handleRequest($request);

        #pr afficher la liste des categories
        $categories = $categorieRepository->findAll();

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
             $user = $this->getUser();
             $post->setUser($user);  
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('app_mes_posts');
        }
        return $this->render('lancer_un_post/newpost.html.twig', [
            'form' => $form->createView(), 
            'categories' => $categories,
        ]);
    }



//LISTES DES POSTS  
    #[Route('/afficherLesPosts', name: 'afficher_les_posts')]
    public function list(CategorieRepository $categorieRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $posts = $entityManager->getRepository(Post::class)->findAll();
        $categories = $categorieRepository->findAll();
        
        // Tableau avec détails de chaque post
        $postDetails = [];
        foreach ($posts as $post) {
            $article = $post->getArticle(); 
            $categorie = $article->getCategorie();
            
            $user = $post->getUser();

            $postDetails[] = [
                'id' => $post->getId(),
                'title' => $article->getTitle(),
                'username' => $post->getUser()->getUsername(),
                'creationDate' => $article->getCreationDate(),
                'name' => $categorie->getName(),
                'content' => $post->getContent(),
                'image' => $post->getImage(), // de $post pour obtenir l'image
            ];
        }
    
        return $this->render('lancer_un_post/list.html.twig', [
            'postDetails' => $postDetails,
            'categories' => $categories,
        ]);
    }
    
    







#[Route('/affichePost/{id}', name: 'app_affiche_un_post')]
public function showpost($id , CategorieRepository $categorieRepository): Response
{
    if ($id == 0) {
        return $this->render('lancer_un_post/post_non_trouve.html.twig', [
            'categories' => $categorieRepository->findAll()
        ]);
    }

    $entityManager = $this->getDoctrine()->getManager();
    $post = $entityManager->getRepository(Post::class)->find($id);
    $categories = $categorieRepository->findAll();

    // Vérifier si le post existe
    if (!$post) {
        return $this->render('lancer_un_post/post_non_trouve.html.twig', [
            'categories' => $categories
        ]);
    }

    // Récupérer l'article et la catégorie associés au post
    $article = $post->getArticle();
    $categorie = $article->getCategorie();
    // Séparer le contenu principal des réponses
    $contentLines = explode("\n", $post->getContent());
    $mainContent = array_shift($contentLines);
    $responses = [];

    foreach ($contentLines as $line) {
        if (strpos($line, ':') !== false) {
            list($username, $content) = explode(':', $line, 2);
            $responses[] = ['username' => trim($username), 'content' => trim($content)];
        }
    }

    // Créer les détails du post
    $postDetails = [
        'id' => $post->getId(),
        'title' => $article->getTitle(),
        'username' => $post->getUser()->getUsername(),
        'creationDate' => $article->getCreationDate(),
        'name' => $categorie->getName(),
        'content' => $mainContent,
        'image' => $post->getImage(),
        'responses' => $responses,
    ];

    return $this->render('lancer_un_post/showpost.html.twig', [
        'postDetails' => $postDetails,
        'categories' => $categories,
    ]);
}







 


#[Route('/repondreAuPost/{id}', name: 'app_repondre_au_post')]
public function repondreAuPost(Request $request, int $id, EntityManagerInterface $entityManager): Response
{
    $post = $entityManager->getRepository(Post::class)->find($id);
    $user = $this->getUser();

    if ($request->isMethod('POST')) {
        $responseContent = $request->request->get('response');
        if (!empty($responseContent)) {
            $currentContent = $post->getContent();
            $newResponse = $user->getUsername() . ': ' . $responseContent;
            $updatedContent = $currentContent . "\n" . $newResponse;
            $post->setContent($updatedContent);
            $entityManager->flush();
            return $this->redirectToRoute('app_affiche_un_post', ['id' => $id]);
        }
    }

    return $this->redirectToRoute('app_affiche_un_post', ['id' => $id]);
}












#[Route('/mes-posts', name: 'app_mes_posts')]
    public function mesPosts(EntityManagerInterface $entityManager,CategorieRepository $categorieRepository ,PostRepository $postRepository,Security $security): Response
    {
        $user = $security->getUser();
        $posts = $postRepository->findBy(['user' => $user]);
        $categories = $categorieRepository->findAll();

        // Vérifier s'il y a des posts
    if (empty($posts)) {
        // Redirection vers une page spécifique si vide
        return $this->redirectToRoute('app_mes_posts_vide');
    }

        return $this->render('lancer_un_post/mesposts.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
        ]);
    }






// Affichage pour POST vide 
#[Route('/mes-posts-vide', name: 'app_mes_posts_vide')]
public function mesPostsVide(CategorieRepository $categorieRepository): Response
{
    // Récupérer toutes les catégories pour affichage
    $categories = $categorieRepository->findAll();

    return $this->render('lancer_un_post/mesposts_vide.html.twig', [
        'categories' => $categories,
    ]);
}



    #[Route('/supprimer-post/{id}', name: 'supprimer_post')]
    public function supprimerPost(int $id, EntityManagerInterface $entityManager,Post $post): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('app_mes_posts');
    }














    /**
     * @Route("/category/success", name="category_success")
     */
    public function success(): Response
    {
        return new Response('Post à été créer avec succès !');
    }

}
