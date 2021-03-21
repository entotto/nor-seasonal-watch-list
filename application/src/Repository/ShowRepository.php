<?php /** @noinspection UnknownInspectionInspection */

/** @noinspection PhpUnused */

namespace App\Repository;

use App\Entity\Season;
use App\Entity\Show;
use App\Entity\User;
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
     * @param User|null $user
     * @param string|null $sortName
     * @return Show[]
     */
    public function getShowsForSeason(
        Season $season,
        ?User $user = null,
        ?string $sortName = 'show_asc'
    ): array {
        $qb = $this->getShowsForSeasonQb($season);
        $this->setOrderByName($qb, $season, $user, $sortName);
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

    private function setOrderByName(
        QueryBuilder $qb,
        ?Season $season = null,
        ?User $user = null,
        ?string $sortName = ''
    ): void
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
                $this->filterByAvgScore($qb, $season);
                $orderBy = 'avg_score';
                $sortOrder = 'desc';
                break;
            case 'statistics_lowest':
                $this->filterByAvgScore($qb, $season);
                $orderBy = 'avg_score';
                $sortOrder = 'asc';
                break;
            case 'activity_highest':
                $this->filterByBestActivity($qb, $season, $user);
                $orderBy = 'activity_rank';
                // 'Best' activity has the lowest rank value
                $sortOrder = 'asc';
                break;
            case 'activity_lowest':
                $this->filterByBestActivity($qb, $season, $user);
                $orderBy = 'activity_rank';
                // 'Worst activity has the highest rank value
                $sortOrder = 'desc';
                break;
            case 'recommendation_highest':
                $this->filterByBestScore($qb, $season, $user);
                $orderBy = 'score_rank';
                // 'Best' score has the lowest rank value
                $sortOrder = 'asc';
                break;
            case 'recommendation_lowest':
                $this->filterByBestScore($qb, $season, $user);
                $orderBy = 'score_rank';
                // 'Worst' score has the highest rank value
                $sortOrder = 'desc';
                break;
            default:
                $orderBy = 's.japaneseTitle';
                $sortOrder = 'asc';
        }
        if ($orderBy !== null) {
            $qb->orderBy($orderBy, $sortOrder);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param Season|null $season
     * @param User|null $user
     */
    private function filterByBestScore(QueryBuilder $qb, ?Season $season, ?User $user): void
    {
        $qb->leftJoin('s.scores', 'scores');
        $qb->leftJoin('scores.score', 'score');
        if ($season !== null) {
            $qb->andWhere('scores.season = :season')
                ->setParameter('season', $season);
        }
        if ($user !== null) {
            $qb->andWhere('scores.user = :user')
                ->setParameter('user', $user);
        }
        $qb->groupBy('s.id');
        $qb->select('s, min(score.rankOrder) AS score_rank');
    }

    /**
     * @param QueryBuilder $qb
     * @param Season|null $season
     * @param User|null $user
     */
    private function filterByBestActivity(QueryBuilder $qb, ?Season $season, ?User $user): void
    {
        $qb->leftJoin('s.scores', 'scores');
        $qb->leftJoin('scores.activity', 'activity');
        if ($season !== null) {
            $qb->andWhere('scores.season = :season')
                ->setParameter('season', $season);
        }
        if ($user !== null) {
            $qb->andWhere('scores.user = :user')
                ->setParameter('user', $user);
        }
        $qb->groupBy('s.id');
        $qb->select('s, min(activity.rankOrder) AS activity_rank');
    }

    /**
     * @param QueryBuilder $qb
     * @param Season|null $season
     */
    private function filterByAvgScore(QueryBuilder $qb, ?Season $season): void
    {
        $qb->leftJoin('s.scores', 'scores');
        $qb->leftJoin('scores.score', 'score');
        if ($season !== null) {
            $qb->andWhere('scores.season = :season')
                ->setParameter('season', $season);
        }
        $qb->andWhere('score.slug != :slug')
            ->setParameter('slug', 'none');
        $qb->groupBy('s.id');
        $qb->select('s, avg(score.value) AS avg_score');
    }
}
