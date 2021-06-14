<?php /** @noinspection PhpPropertyOnlyWrittenInspection */

/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\ElectionVoteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="election_vote",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="election_vote_unique",
 *             columns={"anime_show_id", "season_id", "user_id", "election_id"}
 *         )
 *     }
 * )
 * @ORM\Entity(repositoryClass=ElectionVoteRepository::class)
 */
class ElectionVote
{
    /**
     * @var int|null
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @var Show
     * @ORM\ManyToOne(targetEntity=Show::class, inversedBy="votes")
     * @ORM\JoinColumn(nullable=false)
     */
    private Show $animeShow;

    /**
     * @var Season
     * @ORM\ManyToOne(targetEntity=Season::class, inversedBy="votes")
     * @ORM\JoinColumn(nullable=false)
     */
    private Season $season;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="electionVotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private bool $chosen;

    /**
     * @var Election
     * @ORM\ManyToOne(targetEntity=Election::class, inversedBy="electionVotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private Election $election;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Show
     */
    public function getAnimeShow(): Show
    {
        return $this->animeShow;
    }

    /**
     * @return Show
     */
    public function getShow(): Show
    {
        return $this->getAnimeShow();
    }

    /**
     * @param Show $animeShow
     * @return $this
     */
    public function setAnimeShow(Show $animeShow): self
    {
        $this->animeShow = $animeShow;

        return $this;
    }

    /**
     * @param Show $show
     * @return $this
     */
    public function setShow(Show $show): self
    {
        return $this->setAnimeShow($show);
    }

    /**
     * @return Season
     */
    public function getSeason(): Season
    {
        return $this->season;
    }

    /**
     * @param Season $season
     * @return $this
     */
    public function setSeason(Season $season): self
    {
        $this->season = $season;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return bool
     */
    public function getChosen(): bool
    {
        return $this->chosen;
    }

    /**
     * @param bool $chosen
     * @return $this
     */
    public function setChosen(bool $chosen): self
    {
        $this->chosen = $chosen;

        return $this;
    }

    /**
     * @return Election
     */
    public function getElection(): Election
    {
        return $this->election;
    }

    /**
     * @param Election $election
     * @return $this
     */
    public function setElection(Election $election): self
    {
        $this->election = $election;

        return $this;
    }
}
