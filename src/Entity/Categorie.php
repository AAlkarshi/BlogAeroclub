<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use App\Entity\Article;


#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'categorie', orphanRemoval: true,cascade: ["persist"])]
    private Collection $haves;

   

    public function __construct()
    {
        #permet de gérer les articles qui sont associé
        $this->haves = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getHaves(): Collection
    {
        return $this->haves;
    }


    /**
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    #Ajout des articles
    public function addHafe(Article $hafe): static
    {
        if (!$this->haves->contains($hafe)) {
            $this->haves->add($hafe);
            $hafe->setCategorie($this);
        }

        return $this;
    }

    #Supprimer des articles
    public function removeHafe(Article $hafe): static
    {
        if ($this->haves->removeElement($hafe)) {
            if ($hafe->getCategorie() === $this) {
                $hafe->setCategorie(null);
            }
        }

        return $this;
    }

     /**
     * @param Article $article L'article auquel associer cette catégorie
     * @param User $user L'utilisateur à associer à cette catégorie via l'article
     */
    public function associateWithUser(Article $article, User $user): void
    {
        $article->setUser($user);   // Associe l'utilisateur à l'article
        $this->addHafe($article);   // Associe l'article à cette catégorie
    }

    public function __toString(): string
    {
        return $this->name;
    }

    
    
}
