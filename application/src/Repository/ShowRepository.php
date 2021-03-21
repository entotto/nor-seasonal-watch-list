<?php /** @noinspection UnknownInspectionInspection */

/** @noinspection PhpUnused */

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
     * @param string|null $sortName
     * @return Show[]
     */
    public function getShowsForSeason(
        Season $season,
        ?string $sortName = 'show_asc'
    ): array {
        $qb = $this->getShowsForSeasonQb($season);
        $this->setOrderByName($qb, $season, $sortName);
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
     * @param string|null $sortColumn
     * @param string|null $sortOrder
     * @return Show[]
     */
    public function getShowsForSeasonElectionEligible(
        Season $season,
        ?string $sortColumn = 'romaji',
        ?string $sortOrder = 'ASC'
    ): array {
        $qb = $this->getShowsForSeasonQb($season);
        $qb->andWhere('s.excludeFromElections IS NULL OR s.excludeFromElections = 0');
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

    private function setOrderByName(QueryBuilder $qb, ?Season $season = null, ?string $sortName = ''): void
    {
        switch ($sortName) {
            case 'show_asc':
                $orderBy = 's.japaneseTitle';
                $sortOrder = 'asc';
                break;
            case 'show_desc':
                $orderBy = 's.japaneseTitle';
                $sortOrder = 'desc';
                break;
            case 'statistics_highest':
                $qb->leftJoin('s.scores', 'scores');
                $qb->leftJoin('scores.score', 'score');
                if ($season !== null) {
                    $qb->andWhere('scores.season = :season')
                        ->setParameter('season', $season);
                }
                $qb->groupBy('s.id');
                $qb->select('s, avg(score.value) AS avg_score');
                $orderBy = 'avg_score';
                $sortOrder = 'desc';
                break;
            case 'statistics_lowest':
                $qb->leftJoin('s.scores', 'scores');
                $qb->leftJoin('scores.score', 'score');
                if ($season !== null) {
                    $qb->andWhere('scores.season = :season')
                        ->setParameter('season', $season);
                }
                $qb->groupBy('s.id');
                $qb->select('s, avg(score.value) AS avg_score');
                $orderBy = 'avg_score';
                $sortOrder = 'asc';
                break;
            default:
                $orderBy = null;
                $sortOrder = 'asc';
        }
        if ($orderBy !== null) {
            $qb->orderBy($orderBy, $sortOrder);
        }
    }
}
