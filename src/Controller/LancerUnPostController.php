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
use App\Entity\User;

use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PostRepository;
use App\Repository\UserRepository;



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
        //instancie objet Post à la variable post
        $post = new Post();
        //Récup gestionnaire d'entité Doctrine
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(FormLancerUnPostType::class, $post);
        //traite requete pour les données du form
        $form->handleRequest($request);

        $categories = $categorieRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            // Récup le fichier d'image dans le formulaire
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                //Récup le nom du fichier sans l'extension
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                //slug permet de convertir en URL en une chaine poue la BDD
                $safeFilename = $slugger->slug($originalFilename);
                 // Crée un nom de fichier unique en ajoutant un identifiant et l'extension du fichier
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

            //Execute la requete
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
            
            // Utilisez la relation User -> Post
            $user = $post->getUser();
    
           
            
            $postDetails[] = [
                'id' => $post->getId(),
                'title' => $article->getTitle(),
                'username' => $user->getUsername(), // Utilisez le nom d'utilisateur actuel ici
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
public function showpost($id, CategorieRepository $categorieRepository, UserRepository $userRepository): Response
    {
        // si l'id = 0, alors c'est un post non trouvé
        if ($id == 0) {
            //redirection
            return $this->render('lancer_un_post/post_non_trouve.html.twig', [
                'categories' => $categorieRepository->findAll()
            ]);
        }
         // Récupère Doctrine pour interagir avec BDD
        $entityManager = $this->getDoctrine()->getManager();
        // Récupère le post correspondant à l'id donné depuis la BDD
        $post = $entityManager->getRepository(Post::class)->find($id);
        $categories = $categorieRepository->findAll();

        // Vérifier si le post existe
        if (!$post) {
            return $this->render('lancer_un_post/post_non_trouve.html.twig', [
                'categories' => $categories
            ]);
        }
        // Récup l'article et la catégorie associés au post
        $article = $post->getArticle();
          // Récup la catégorie associée à l'article
        $categorie = $article->getCategorie();

        // Séparer le contenu principal des réponses en sautant une ligne
        $contentLines = explode("\n", $post->getContent());

        // Récupère le contenu principal
        $mainContent = array_shift($contentLines);
        $responses = [];

        foreach ($contentLines as $line) {
            // Vérifier si la ligne contient un séparateur (le nom d'utilisateur)
            if (strpos($line, ':') !== false) {
                // Diviser la ligne en 2 avec explode pour obtenir le nom d'utilisateur et le contenu de la réponse
                list($username, $content) = explode(':', $line, 2);
    
                // Rechercher l'utilisateur associé à la réponse
                $user = $userRepository->findOneBy(['username' => trim($username)]);
    
                // Vérifier si l'utilisateur existe et n'est pas supprimé
                if ($user) {
                    $responses[] = ['username' => trim($username), 'content' => trim($content)];
                }
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

    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé');
    }

    if (!$post) {
        throw $this->createNotFoundException('Post non trouvé');
    }

    if ($request->isMethod('POST')) {
        $responseContent = $request->request->get('response');
        if (!empty($responseContent)) {
            // Créer la nouvelle réponse avec le nouveau nom d'utilisateur
            $newResponse = $user->getUsername() . ': ' . $responseContent;

            // MAJ le contenu du post avec la nouvelle réponse
            $updatedContent = $post->getContent() . "\n" . $newResponse;
            $post->setContent($updatedContent);

            // MAJ toutes les réponses perso dans le contenu du post
            $contentLines = explode("\n", $post->getContent());
            $mainContent = array_shift($contentLines);
            $responses = [];

            foreach ($contentLines as $line) {
                if (strpos($line, ':') !== false) {
                    list($username, $content) = explode(':', $line, 2);
                    // MAJ le nom d'utilisateur dans chaque réponse existante
                    if (trim($username) === $user->getUsername()) {
                        $line = $user->getUsername() . ':' . trim($content);
                    }
                    $responses[] = $line;
                }
            }

            // Reconstruire le contenu MAJ en sautant une ligne avec les nouvelles réponses
            $updatedContent = $mainContent . "\n" . implode("\n", $responses);

            // MAJ le contenu du post avec le nouveau contenu 
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
