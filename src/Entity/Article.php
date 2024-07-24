<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Categorie;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de création ne peut pas être vide.")]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\ManyToOne(inversedBy: 'haves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

 #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'article',cascade: ["remove"], orphanRemoval: true)]
    private Collection $belongs;

    public function __construct()
    {
        $this->belongs = new ArrayCollection();
        $this->creationDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): static
    {
        $this->creationDate = $creationDate;
        return $this;
    }

#permet de gérer la relation 1 à plusieurs entre entité Article et POST qui est une Collections d'objet
    /**
     * @return Collection<int, Post>
     */
    public function getBelongs(): Collection
    {
        return $this->belongs;
    }

#Ajoute un objet Post à la collection
    public function addBelong(Post $belong): static
    {
        //vérifie si ds collection y a un objet type POST
        if (!$this->belongs->contains($belong)) {
            $this->belongs->add($belong);
            $belong->setArticle($this);
        }
        return $this;
    }

#Supprime un objet Post à la collection
    public function removeBelong(Post $belong): static
    {
        if ($this->belongs->removeElement($belong)) {
            if ($belong->getArticle() === $this) {
                $belong->setArticle(null);
            }
        }
        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
}
