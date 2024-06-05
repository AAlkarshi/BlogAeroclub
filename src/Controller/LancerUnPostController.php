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
             $user = $this->/*security->*/getUser();
             $post->setUser($user);  

            /*// Ajouter une nouvelle ligne dans le contenu du post
            $post->setContent("\n");
            */
            
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
            $categorie = $article->getCategorie();
            
            $user = $post->getUser();

            $postDetails[] = [
                'id' => $post->getId(),
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
    
    





//AFFICHER UN POST EN PARTICULIER en mettant son ID de la table POST
#[Route('/affichePost/{id}', name: 'app_affiche_un_post')]
public function showpost(Request $request, SluggerInterface $slugger, int $id): Response
{
    // Récupérer l'utilisateur actuel
    $user = $this->getUser();  
    $entityManager = $this->getDoctrine()->getManager();
    
    // Trouver le post par son id
    $post = $entityManager->getRepository(Post::class)->find($id);
   
    
    // Récupérer l'article et la catégorie associés au post
    $article = $post->getArticle(); 
    $categorie = $article->getCategorie(); 

    // Créer les détails du post
    $postDetails = [
        'id' => $post->getId(),
        'title' => $article->getTitle(),
        'username' => $post->getUser()->getUsername(),   //de post je recupere user et donc son USERNAME
        'creationDate' => $article->getCreationDate(),
        'name' => $categorie->getName(),
        'content' => $post->getContent(),
        'image' => $post->getImage(),
    ];

    return $this->render('lancer_un_post/showpost.html.twig', [
        'postDetails' => $postDetails,
    ]);      
}





 //AJOUTER UNE REPONSE A UN POST
 #[Route('/repondreAuPost{id}', name: 'app_repondre_au_post')]
public function repondreAuPost(Request $request, int $id, EntityManagerInterface $entityManager): Response
{
    $entityManager = $this->getDoctrine()->getManager();
    $post = $entityManager->getRepository(Post::class)->find($id);

    // Récupérer l'utilisateur actuel
    $user = $this->getUser();  

    // Traiter la réponse de l'utilisateur
    if ($request->isMethod('POST')) {

        //REQUETE pour la reponse
        $responseContent = $request->request->get('response');
        if (!empty($responseContent)) {
            $currentContent = $post->getContent();

            //Nouvelle reponse PSEUDO : rajoute le nouveau contenu
            $newResponse = $this->getUser()->getUsername() . ':' . $responseContent . "\n";

            //MAJ du contenu avc contenu actuel saute ligne et met le nouveau contenu en BDD et me le rajoute à coté
            $updatedContent = $currentContent . "\n" . $newResponse;
            
            //Modifier le Contenu déja présent
            $post->setContent($updatedContent);

            // Enregistrer les modifications
            $entityManager->flush();
            //Redirige vers cette mm page
            return $this->redirectToRoute('app_repondre_au_post', ['id' => $id]);
            }
    }
    // Récupérer ARTICLE associés à post et CATEGORIE associés à article 
    $article = $post->getArticle(); 
    $categorie = $article->getCategorie(); 

    return $this->redirectToRoute('app_affiche_un_post', ['id' => $id]);
}

 


























    /**
     * @Route("/category/success", name="category_success")
     */
    public function success(): Response
    {
        return new Response('Post à été créer avec succès !');
    }

}
