<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Categorie;
use App\Entity\Article;
use App\Entity\Post;




#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "user")]

// montre que ce NOM user est UNIQUE
#[UniqueEntity(fields: ['username'], message: 'Il existe déjà un compte avec ce nom d\'utilisateur')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;
    

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string 
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    

    
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'expediteur', orphanRemoval: true)]
private Collection $sentMessages;

#[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'destinataire', orphanRemoval: true)]
private Collection $receivedMessages;

    #[ORM\ManyToMany(targetEntity: self::class)]
    #[ORM\JoinTable(
        name: 'user_friends',
        joinColumns: [new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'friend_user_id', referencedColumnName: 'id')]
    )]
    private Collection $friends;

    //RELATIONS Collection est une structure de donnée qui représente un groupe d'objet
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'user', orphanRemoval: true)]
private Collection $articles;

// Pour Posts
#[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'user', orphanRemoval: true)]
private Collection $posts;
    
    #[ORM\ManyToMany(targetEntity:User::class, mappedBy:"receivedFriendRequests")]
    private $sentFriendRequests;


    
    #[ORM\ManyToMany(targetEntity:User::class, inversedBy:"sentFriendRequests")]
      #[ORM\JoinTable(
          name:"user_friend_requests",
          joinColumns: [new ORM\JoinColumn(name:"sender_id", referencedColumnName:"id")],
          inverseJoinColumns: [new ORM\JoinColumn(name:"receiver_id", referencedColumnName:"id")]
      )]
    private Collection $receivedFriendRequests;




    //initialise articles et post
    public function __construct()
    {
        /* ArrayCollection pour manipuler facilement ces collections */
        $this->articles = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
        $this->friends = new ArrayCollection();
        $this->myFriends = new ArrayCollection();
        $this->sentFriendRequests = new ArrayCollection();
        $this->receivedFriendRequests = new ArrayCollection();
    }


    /* FRIENDS */
     /**
     * @return Collection|User[]
     */
    public function getFriends(): Collection
    {
        return $this->friends;
    }

    public function addFriend(User $friend): self
    {
        if (!$this->friends->contains($friend)) {
            $this->friends[] = $friend;
        }
        return $this;
    }

    public function removeFriend(User $friend): self
    {
        $this->friends->removeElement($friend);
        return $this;
    }



    /* REQUETE DEMANDE AMI */
    public function getSentFriendRequests(): Collection
    {
        return $this->sentFriendRequests;
    }

    public function addSentFriendRequest(User $user): self
    {
        if (!$this->sentFriendRequests->contains($user)) {
            $this->sentFriendRequests[] = $user;
        }
        return $this;
    }

    public function removeSentFriendRequest(User $user): self
    {
        $this->sentFriendRequests->removeElement($user);

        return $this;
    }








    /* MESSAGE ENVOYER */
     /**
     * @return Collection|Message[]
     */
    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    public function addSentMessage(Message $sentMessage): self
    {
        if (!$this->sentMessages->contains($sentMessage)) {
            $this->sentMessages[] = $sentMessage;
            $sentMessage->setExpediteur($this);
        }
        return $this;
    }

    public function removeSentMessage(Message $sentMessage): self
    {
        if ($this->sentMessages->removeElement($sentMessage)) {
            if ($sentMessage->getExpediteur() === $this) {
                $sentMessage->setExpediteur(null);
            }
        }

        return $this;
    }

    /* RECEVOIR MESSAGE */
    /**
     * @return Collection|Message[]
     */
    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }

    public function addReceivedMessage(Message $receivedMessage): self
    {
        if (!$this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages[] = $receivedMessage;
            $receivedMessage->setDestinataire($this);
        }

        return $this;
    }

    public function removeReceivedMessage(Message $receivedMessage): self
    {
        if ($this->receivedMessages->removeElement($receivedMessage)) {
            if ($receivedMessage->getDestinataire() === $this) {
                $receivedMessage->setDestinataire(null);
            }
        }
        return $this;
    }



    public function getId(): ?int
    {
        return $this->id;
    }

   
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    



    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }


    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }





    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }















    
    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }








    public function getReceivedFriendRequests(): Collection
    {
        return $this->receivedFriendRequests;
    }

    public function addReceivedFriendRequest(User $user): self
    {
        if (!$this->receivedFriendRequests->contains($user)) {
            $this->receivedFriendRequests[] = $user;
        }
        return $this;
    }

    public function removeReceivedFriendRequest(User $user): self
    {
        $this->receivedFriendRequests->removeElement($user);
        return $this;
    }








    
    /**
     *  "salt" est un algorithme de hachage moderne pour MDP dans votre fichier security.yaml.
    *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /* Ajouter par symfony afin d'effacer tt donnée apres authentification */
    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


    

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    /*ARTICLE  */
    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setUser($this);
        }
        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            if ($article->getUser() === $this) {
                $article->setUser(null);
            }
        }
        return $this;
    }





    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setUser($this);
        }
        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }
        return $this;
    }
}
