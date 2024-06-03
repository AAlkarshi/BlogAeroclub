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

    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'categorie', orphanRemoval: true)]
    private Collection $haves;

    #private $articles;


    public function __construct()
    {
        $this->haves = new ArrayCollection();

       # $this->articles = new ArrayCollection();
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


    public function addHafe(Article $hafe): static
    {
        if (!$this->haves->contains($hafe)) {
            $this->haves->add($hafe);
            $hafe->setCategorie($this);
        }

        return $this;
    }

    public function removeHafe(Article $hafe): static
    {
        if ($this->haves->removeElement($hafe)) {
            // set the owning side to null (unless already changed)
            if ($hafe->getCategorie() === $this) {
                $hafe->setCategorie(null);
            }
        }

        return $this;
    }



    public function __toString(): string
    {
        return $this->name;
    }

    
    
}
