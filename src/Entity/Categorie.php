<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;


#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'categorie', cascade: ["persist"], orphanRemoval: true)]
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

    
    public function getHaves(): Collection
    {
        return $this->haves;
    }

    public function addHave(Have $have): self
    {
        if (!$this->haves->contains($have)) {
            $this->haves[] = $have;
            $have->setCategorie($this);
        }

        return $this;
    }

    public function removeHave(Article $article): self
    {
        if ($this->haves->removeElement($article)) {
            if ($article->getCategorie() === $this) {
                $article->setCategorie(null);
            }
        }
    
        return $this;
    }
    

     

    public function __toString(): string
    {
        return $this->name;
    }

    
    
}
