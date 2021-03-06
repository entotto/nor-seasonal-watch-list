<?php

namespace App\Repository;

use App\Entity\Season;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method Season|null find($id, $lockMode = null, $lockVersion = null)
 * @method Season|null findOneBy(array $criteria, array $orderBy = null)
 * @method Season[]    findAll()
 * @method Season[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Season::class);
    }

    public function getAllInRankOrder(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.rankOrder', 'asc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param DateTime|null $date
     * @return Season|null
     */
    public function getSeasonForDate(?DateTime $date = null): ?season
    {
        try {
            $date = $date ?? new DateTime();
            $year = (int)$date->format('Y');
            $month = (int)$date->format('m');
            switch ($month) {
                case 1:
                case 2:
                case 3:
                    $quarter = 'Winter';
                    break;
                case 4:
                case 5:
                case 6:
                    $quarter = 'Spring';
                    break;
                case 7:
                case 8:
                case 9:
                    $quarter = 'Summer';
                    break;
                case 10:
                case 11:
                case 12:
                    $quarter = 'Fall';
                    break;
                default:
                    return null;
            }
            return $this->findOneBy(['year' => $year, 'yearPart' => $quarter]);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @return Season|null
     * @throws NonUniqueResultException
     */
    public function getFirstSeason(): ?season
    {
        return $this->createQueryBuilder('s')
            ->orderBy('rankOrder', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return Season[] Returns an array of Season objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Season
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
