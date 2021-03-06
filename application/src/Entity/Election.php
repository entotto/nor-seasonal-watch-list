<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\ElectionRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @ORM\Entity(repositoryClass=ElectionRepository::class)
 */
class Election
{
    /**
     * @var int|null
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @var Season
     * @ORM\ManyToOne(targetEntity=Season::class, inversedBy="elections")
     * @ORM\JoinColumn(nullable=false)
     */
    private Season $season;

    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $startDate;

    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $endDate;

    /**
     * @ORM\OneToMany(targetEntity=ElectionVote::class, mappedBy="election", orphanRemoval=true)
     */
    private Collection $electionVotes;

    /**
     * Election constructor.
     */
    public function __construct()
    {
        $this->electionVotes = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return DateTimeInterface
     */
    public function getStartDate(): DateTimeInterface
    {
        return $this->startDate;
    }

    /**
     * @param DateTimeInterface $startDate
     * @return $this
     */
    public function setStartDate(DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getEndDate(): DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * @param DateTimeInterface $endDate
     * @return $this
     */
    public function setEndDate(DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        try {
            $now = new DateTime();
            return $now >= $this->getStartDate() && $now <= $this->getEndDate();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return Collection|ElectionVote[]
     */
    public function getElectionVotes(): Collection
    {
        return $this->electionVotes;
    }

    /**
     * @param ElectionVote $showSeasonVote
     * @return $this
     */
    public function addElectionVote(ElectionVote $showSeasonVote): self
    {
        if (!$this->electionVotes->contains($showSeasonVote)) {
            $this->electionVotes[] = $showSeasonVote;
            $showSeasonVote->setElection($this);
        }

        return $this;
    }

    /**
     * @param ElectionVote $showSeasonVote
     * @return $this
     */
    public function removeElectionVote(ElectionVote $showSeasonVote): self
    {
        if ($this->electionVotes->removeElement($showSeasonVote)) {
            // set the owning side to null (unless already changed)
            /** @noinspection NestedPositiveIfStatementsInspection */
            if ($showSeasonVote->getElection() === $this) {
                $showSeasonVote->setElection(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getSeason()->getName() . $this->getStartDate()->format(' \(Y-m-d H:i:s\)');
    }
}
