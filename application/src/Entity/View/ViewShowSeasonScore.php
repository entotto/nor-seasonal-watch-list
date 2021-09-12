<?php /** @noinspection UnknownInspectionInspection */

/** @noinspection PhpUnused */

namespace App\Entity\View;

use App\Entity\Season;
use App\Entity\Show;
use App\Entity\User;

class ViewShowSeasonScore
{
    /**
     * @var int|null
     */
    private ?int $id = null;

    /**
     * @var int|null
     */
    private ?int $score = null;

    /**
     * @var string|null
     */
    private ?string $scoreName = null;

    /**
     * @var Season|null
     */
    private ?Season $season = null;

    /**
     * @var Show|null
     */
    private ?Show $show = null;

    /**
     * @var User|null
     */
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getScore(): ?int
    {
        return $this->score;
    }

    /**
     * @param int|null $score
     */
    public function setScore(?int $score): void
    {
        $this->score = $score;
    }

    public function getScoreName(): ?string
    {
        return $this->scoreName;
    }

    public function setScoreName(?string $scoreName): self
    {
        $this->scoreName = $scoreName;

        return $this;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): self
    {
        $this->season = $season;

        return $this;
    }

    public function getShow(): ?Show
    {
        return $this->show;
    }

    public function setShow(?Show $show): self
    {
        $this->show = $show;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
