<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

namespace App\Repository;

use App\Entity\Season;
use App\Entity\Show;
use App\Entity\ShowSeasonScore;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;

/**
 * @method ShowSeasonScore|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShowSeasonScore|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShowSeasonScore[]    findAll()
 * @method ShowSeasonScore[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShowSeasonScoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShowSeasonScore::class);
    }

    /**
     * @param Season $season
     * @param Show $show
     * @return QueryBuilder
     */
    private function findAllForSeasonAndShowQb(Season $season, Show $show): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->where('s.season = :season')
            ->andWhere('s.show = :show')
            ->setParameter('season', $season)
            ->setParameter('show', $show);
    }

    /**
     * @param Season $season
     * @param Show $show
     * @param string $sortOrder
     * @param string $sortDirection
     * @return ShowSeasonScore[]
     */
    public function findAllForSeasonAndShow(
        Season $season,
        Show $show,
        string $sortOrder = 'username',
        string $sortDirection = 'ASC'
    ): array {
        /** @noinspection DegradedSwitchInspection */
        switch ($sortOrder) {
            case 'username':
                $orderColumn = 'u.username';
                break;
            default:
                throw new RuntimeException('Unknown sort order requested.');
        }
        return $this->findAllForSeasonAndShowQb($season, $show)
            ->join('s.user', 'u')
            ->orderBy($orderColumn, $sortDirection)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Season $season
     * @param Show $show
     * @return ShowSeasonScore[]
     */
    public function getCountsForSeasonAndShow(Season $season, Show $show): array
    {
        return $this->findAllForSeasonAndShowQb($season, $show)
            ->select('count(s.score) as scoreCount, s.score as score, s.scoreName as scoreName')
            ->groupBy('s.score')
            ->addGroupBy('s.scoreName')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param Season $season
     * @return ShowSeasonScore[]
     */
    public function findAllForUserAndSeason(User $user, Season $season): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.season = :season')
            ->setParameter('user', $user)
            ->setParameter('season', $season)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param Show $show
     * @param Season $season
     * @return ShowSeasonScore|null
     * @throws NonUniqueResultException
     */
    public function getForUserAndShowAndSeason(User $user, Show $show, Season $season): ?ShowSeasonScore
    {
        return $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.show = :show')
            ->andWhere('s.season = :season')
            ->setParameter('user', $user)
            ->setParameter('show', $show)
            ->setParameter('season', $season)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param User $user
     * @param Season $season
     * @param string $showOrder
     * @param string $showDirection
     * @return ShowSeasonScore[]
     */
    public function getForUserAndSeason(
        User $user,
        Season $season,
        string $showOrder='romaji',
        string $showDirection='ASC'
    ): array {
        $orderColumn = ($showOrder === 'romaji' ? 'show.japaneseTitle' : 'show.englishTitle');
        $orderDirection = strtoupper($showDirection) === 'ASC' ? 'ASC' : 'DESC';
        return $this->createQueryBuilder('sss')
            ->join('sss.show', 'show')
            ->where('sss.user = :user')
            ->andWhere('sss.season = :season')
            ->setParameter('user', $user)
            ->setParameter('season', $season)
            ->orderBy($orderColumn, $orderDirection)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Season $season
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function getCountsForSeason(Season $season): array
    {
        $sql = <<<EOF
SELECT
       sss.show_id,
       count(s_yes.id) AS yes_count,
       count(s_no.id) AS no_count,
       count(s_disliked.id) AS disliked_count,
       count(s_dropped.id) AS dropped_count,
       count(s_ptw.id) AS ptw_count,
       count(s_watching.id) AS watching_count,
       count(s_suggested.id) AS suggested_count,
       count(s_th8a.id) AS th8a_count,
       count(s_all.value) AS all_count,
       sum(s_all.value) AS score_total
FROM show_season_score sss
    LEFT JOIN score s_yes ON sss.score_id = s_yes.id AND s_yes.value >= 2
    LEFT JOIN score s_no ON sss.score_id = s_no.id AND s_no.value < 0
    LEFT JOIN score s_disliked on sss.score_id = s_disliked.id AND s_disliked.slug = 'disliked'
    LEFT JOIN score s_dropped on sss.score_id = s_dropped.id AND s_dropped.slug = 'dropped'
    LEFT JOIN score s_ptw on sss.score_id = s_ptw.id AND s_ptw.slug = 'ptw'
    LEFT JOIN score s_watching on sss.score_id = s_watching.id AND s_watching.slug = 'watching'
    LEFT JOIN score s_suggested on sss.score_id = s_suggested.id AND s_suggested.slug = 'suggested'
    LEFT JOIN score s_th8a ON sss.score_id = s_th8a.id AND s_th8a.slug = 'th8a'
    LEFT JOIN score s_all ON sss.score_id = s_all.id
WHERE sss.season_id = :season_id
GROUP BY sss.show_id;
EOF;
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(['season_id' => $season->getId()]);
        return $stmt->fetchAllAssociative();
    }

    // /**
    //  * @return ShowSeasonScore[] Returns an array of ShowSeasonScore objects
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
    public function findOneBySomeField($value): ?ShowSeasonScore
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
