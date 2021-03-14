<?php /** @noinspection UnknownInspectionInspection */

/** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\ShowSeasonScoreRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="show_season_score",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="show_season_score_unique",
 *             columns={"season_id", "show_id", "user_id"}
 *         )
 *     }
 * )
 * @ORM\Entity(repositoryClass=ShowSeasonScoreRepository::class)
 */
class ShowSeasonScore
{
    /**
     * @var int|null
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @var Activity|null
     * @ORM\ManyToOne(targetEntity=Activity::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Activity $activity;

    /**
     * @var Score|null
     * @ORM\ManyToOne(targetEntity=Score::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Score $score;

    /**
     * @var Season
     * @ORM\ManyToOne(targetEntity=Season::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Season $season;

    /**
     * @var Show
     * @ORM\ManyToOne(targetEntity=Show::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Show $show;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="showSeasonScores")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Activity|null
     */
    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    /**
     * @param Activity|null $activity
     */
    public function setActivity(?Activity $activity): void
    {
        $this->activity = $activity;
    }

    /**
     * @return Score|null
     */
    public function getScore(): ?Score
    {
        return $this->score;
    }

    /**
     * @param Score|null $score
     */
    public function setScore(?Score $score): void
    {
        $this->score = $score;
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
     * @return Show
     */
    public function getShow(): Show
    {
        return $this->show;
    }

    /**
     * @param Show $show
     * @return $this
     */
    public function setShow(Show $show): self
    {
        $this->show = $show;

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
}
