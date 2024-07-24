<?php

namespace App\Entity;

use App\Repository\UserFriendQueryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserFriendQueryRepository::class)]
#[ORM\Table(name: "user_friend_query")]
class UserFriendQuery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sentFriendQueries')]
    #[ORM\JoinColumn(nullable: false, name: "sender_id", referencedColumnName: "id")]
    private ?User $sender = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'receivedFriendQueries')]
    #[ORM\JoinColumn(nullable: false, name: "receiver_id", referencedColumnName: "id")]
    private ?User $receiver = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }
}
