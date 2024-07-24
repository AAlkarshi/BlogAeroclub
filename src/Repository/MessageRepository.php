<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }


    /**
     * Récupère les conversations de l'utilisateur connecté
     */
    public function findConversationsForUser(User $user)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.expediteur', 'expediteur')
            ->leftJoin('m.destinataire', 'destinataire')
            ->where('expediteur = :user OR destinataire = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }


    /**
     * Récupère tous les utilisateurs avec qui l'utilisateur a eu des conversations
     */
    public function findMessagesBetweenUsers(User $user1, User $user2)
    {
        return $this->createQueryBuilder('m')
            ->where('m.expediteur = :user1 AND m.destinataire = :user2')
            ->orWhere('m.expediteur = :user2 AND m.destinataire = :user1')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }


    

/**
     * Récupère les utilisateurs uniques avec qui l'utilisateur a eu des conversations
     */
    public function findUniqueConversationsForUser(User $user)
    {
        return $this->createQueryBuilder('m')
            ->select('DISTINCT u.id, u.username')
            ->innerJoin('m.expediteur', 'e')
            ->innerJoin('m.destinataire', 'd')
            ->innerJoin(User::class, 'u', 'WITH', 'u = e OR u = d')
            ->where('e = :user OR d = :user')
            ->andWhere('u != :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    


    
    
   
}
