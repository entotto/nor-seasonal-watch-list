<?php

namespace App\Repository;

use App\Entity\Election;
use App\Entity\ElectionVote;
use App\Entity\Show;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception as DoctrineException;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ElectionVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method ElectionVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method ElectionVote[]    findAll()
 * @method ElectionVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ElectionVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElectionVote::class);
    }

    /**
     * @param User $user
     * @param Show $show
     * @param Election $election
     * @return ElectionVote|null
     * @throws NonUniqueResultException
     */
    public function getForUserAndShowAndElection(
        User $user,
        Show $show,
        Election $election
    ): ?ElectionVote {
        return $this->createQueryBuilder('ev')
            ->where('ev.user = :user')
            ->andWhere('ev.animeShow = :show')
            ->andWhere('ev.election = :election')
            ->setParameter('user', $user)
            ->setParameter('show', $show)
            ->setParameter('election', $election)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Election $election
     * @return array
     * @throws DoctrineException
     * @throws Exception
     */
    public function getCountsForElection(
        Election $election
    ): array {
        $sql = <<<EOF
SELECT COUNT(ev.id) AS vote_count,
ev.election_id AS election_id,
ev.anime_show_id AS show_id,
s.japanese_title AS japanese_title,
s.english_title AS english_title,
s.full_japanese_title AS full_japanese_title
FROM election_vote ev
JOIN anime_show s ON s.id = ev.anime_show_id
WHERE ev.chosen = 1
AND ev.election_id = :election_id
GROUP BY election_id, show_id, japanese_title, english_title, full_japanese_title
ORDER BY vote_count DESC
EOF;
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(['election_id' => $election->getId()]);
        return $stmt->fetchAllAssociative();
    }
}
