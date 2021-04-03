<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param string|null $sortColumn
     * @param string|null $sortDirection
     * @return User[]
     */
    public function getAllSorted(?string $sortColumn='username', ?string $sortDirection='ASC'): array
    {
        $qb = $this->createQueryBuilder('u');
        if ($sortColumn !== null) {
            $qb->orderBy("u.{$sortColumn}", $sortDirection);
        }
        return $qb->getQuery()->getResult();
    }
}
