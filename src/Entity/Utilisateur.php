<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
#use Symfony\Component\Security\Core\User\UserInterface;


#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
  
#AJOUT DE MA PART implements UserInterface
class Utilisateur #implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column]
    private array $role = [];

    #[ORM\Column]
    private ?bool $isVerified = null;

    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'utilisateur', orphanRemoval: true)]
    private Collection $writes;

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'utilisateur', orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->writes = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): array
    {
        return $this->role;
    }

    public function setRole(array $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function isIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
    

    /**
     * @return Collection<int, Article>
     */
    public function getWrites(): Collection
    {
        return $this->writes;
    }

    public function addWrite(Article $write): static
    {
        if (!$this->writes->contains($write)) {
            $this->writes->add($write);
            $write->setUtilisateur($this);
        }

        return $this;
    }

    public function removeWrite(Article $write): static
    {
        if ($this->writes->removeElement($write)) {
            // set the owning side to null (unless already changed)
            if ($write->getUtilisateur() === $this) {
                $write->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Post $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setUtilisateur($this);
        }

        return $this;
    }

    public function removeComment(Post $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUtilisateur() === $this) {
                $comment->setUtilisateur(null);
            }
        }

        return $this;
    }
}
