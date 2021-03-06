<?php

namespace App\Repository;

use App\Entity\Season;
use App\Entity\Show;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Show|null find($id, $lockMode = null, $lockVersion = null)
 * @method Show|null findOneBy(array $criteria, array $orderBy = null)
 * @method Show[]    findAll()
 * @method Show[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Show::class);
    }

    /**
     * @param string|null $sortColumn
     * @param string|null $sortOrder
     * @return Show[]
     */
    public function getShowsSorted(
        ?string $sortColumn = 'romaji',
        ?string $sortOrder = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('s');
        $this->setOrderBy($qb, $sortColumn, $sortOrder);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param Season $season
     * @param string|null $sortColumn
     * @param string|null $sortOrder
     * @return Show[]
     */
    public function getShowsForSeason(
        Season $season,
        ?string $sortColumn = 'romaji',
        ?string $sortOrder = 'ASC'
    ): array {
        $qb = $this->getShowsForSeasonQb($season);
        $this->setOrderBy($qb, $sortColumn, $sortOrder);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param Season $season
     * @param string|null $sortColumn
     * @param string|null $sortOrder
     * @return Show[]
     */
    public function getShowsForSeasonWithNoChannel(
        Season $season,
        ?string $sortColumn = 'romaji',
        ?string $sortOrder = 'ASC'
    ): array {
        $qb = $this->getShowsForSeasonQb($season);
        $qb->leftJoin("s.discordChannel", 'discordChannel');
        $qb->andWhere('discordChannel IS NULL');
        $this->setOrderBy($qb, $sortColumn, $sortOrder);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param Season $season
     * @return QueryBuilder
     */
    private function getShowsForSeasonQb(Season $season): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->join('s.seasons', 'seasons')
            ->where('seasons = :season')
            ->setParameter('season', $season);
    }

    /**
     * @param QueryBuilder $qb
     * @param string|null $sortColumn
     * @param string|null $sortOrder
     */
    private function setOrderBy(QueryBuilder $qb, ?string $sortColumn, ?string $sortOrder): void
    {
        switch ($sortColumn) {
            case 'english':
                $orderBy = 'englishTitle';
                break;
            case 'kanji':
                $orderBy = 'fullJapaneseTitle';
                break;
            case '':
            case null:
            case 'none':
                $orderBy = null;
                break;
            default:
                $orderBy = 'japaneseTitle';
        }
        if ($orderBy !== null) {
            $qb->orderBy("s.{$orderBy}", $sortOrder);
        }
    }
}
