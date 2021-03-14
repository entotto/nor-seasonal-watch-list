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
    ss.show_id,
    coalesce(yes_j.my_count, 0) as yes_count,
    coalesce(no_j.my_count, 0) as no_count,
    coalesce(disliked_j.my_count, 0) as disliked_count,
    coalesce(dropped_j.my_count, 0) as dropped_count,
    coalesce(ptw_j.my_count, 0) as ptw_count,
    coalesce(watching_j.my_count, 0) as watching_count,
    coalesce(suggested_j.my_count, 0) as suggested_count,
    coalesce(th8a_j.my_count, 0) as th8a_count,
    coalesce(disliked_j.my_count, 0) +
        coalesce(dropped_j.my_count, 0) +
        coalesce(ptw_j.my_count, 0) +
        coalesce(watching_j.my_count, 0) +
        coalesce(suggested_j.my_count, 0) +
        coalesce(th8a_j.my_count, 0) AS all_count,
    coalesce(score_j.my_total, 0) as score_total
FROM show_season ss
LEFT JOIN (
  SELECT 
      sss1.show_id AS show_id,
      count(*) AS my_count
  FROM show_season_score sss1
  JOIN score s1 ON sss1.score_id = s1.id AND s1.value >= 2
  GROUP BY sss1.show_id
) AS yes_j ON yes_j.show_id = ss.show_id
LEFT JOIN (
    SELECT
        sss2.show_id AS show_id,
        count(*) AS my_count
    FROM show_season_score sss2
    JOIN score s2 on sss2.score_id = s2.id AND s2.value < 0
    GROUP BY sss2.show_id
) AS no_j ON no_j.show_id = ss.show_id
LEFT JOIN (
    SELECT
        sss3.show_id AS show_id,
        count(*) AS my_count
    FROM show_season_score sss3
    JOIN score s3 on sss3.score_id = s3.id AND s3.slug = 'disliked'
    GROUP BY sss3.show_id
) AS disliked_j ON disliked_j.show_id = ss.show_id
LEFT JOIN (
    SELECT
        sss4.show_id AS show_id,
        count(*) AS my_count
    FROM show_season_score sss4
    JOIN score s4 on sss4.score_id = s4.id AND s4.slug = 'dropped'
    GROUP BY sss4.show_id
) AS dropped_j ON dropped_j.show_id = ss.show_id
LEFT JOIN (
    SELECT
        sss5.show_id AS show_id,
        count(*) AS my_count
    FROM show_season_score sss5
    JOIN score s5 on sss5.score_id = s5.id AND s5.slug = 'ptw'
    GROUP BY sss5.show_id
) AS ptw_j ON ptw_j.show_id = ss.show_id
LEFT JOIN (
    SELECT
        sss6.show_id AS show_id,
        count(*) AS my_count
    FROM show_season_score sss6
    JOIN score s6 on sss6.score_id = s6.id AND s6.slug = 'watching'
    GROUP BY sss6.show_id
) AS watching_j ON watching_j.show_id = ss.show_id
LEFT JOIN (
    SELECT
        sss7.show_id AS show_id,
        count(*) AS my_count
    FROM show_season_score sss7
    JOIN score s7 on sss7.score_id = s7.id AND s7.slug = 'suggested'
    GROUP BY sss7.show_id
) AS suggested_j ON suggested_j.show_id = ss.show_id
LEFT JOIN (
    SELECT
        sss8.show_id AS show_id,
        count(*) AS my_count
    FROM show_season_score sss8
    JOIN score s8 on sss8.score_id = s8.id AND s8.slug = 'th8a'
    GROUP BY sss8.show_id
) AS th8a_j ON th8a_j.show_id = ss.show_id
LEFT JOIN (
    SELECT
        sss9.show_id AS show_id,
        sum(s9.value) as my_total
    FROM show_season_score sss9
    JOIN score s9 on sss9.score_id = s9.id
    GROUP BY sss9.show_id
) AS score_j ON score_j.show_id = ss.show_id
WHERE ss.season_id = :season_id
;


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
