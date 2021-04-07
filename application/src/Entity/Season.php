<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnusedAliasInspection */
/** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonException;

/**
 * @ORM\Entity(repositoryClass=SeasonRepository::class)
 */
class Season
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $year;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $yearPart;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $rankOrder;

    /**
     * @ORM\ManyToMany(targetEntity=Show::class, mappedBy="seasons")
     */
    private Collection $shows;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity=Election::class, mappedBy="Season", orphanRemoval=true)
     */
    private Collection $elections;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity=DiscordChannel::class, mappedBy="season", orphanRemoval=true)
     */
    private $discordChannels;

    public function __construct()
    {
        $this->shows = new ArrayCollection();
        $this->elections = new ArrayCollection();
        $this->discordChannels = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getYearPart(): ?string
    {
        return $this->yearPart;
    }

    /**
     * @param string $yearPart
     * @return $this
     */
    public function setYearPart(string $yearPart): self
    {
        $this->yearPart = $yearPart;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRankOrder(): ?int
    {
        return $this->rankOrder;
    }

    /**
     * @param int $rankOrder
     * @return $this
     */
    public function setRankOrder(int $rankOrder): self
    {
        $this->rankOrder = $rankOrder;

        return $this;
    }

    /**
     * @return Collection|Show[]
     */
    public function getShows(): Collection
    {
        return $this->shows;
    }

    /**
     * @param Show $show
     * @return $this
     */
    public function addShow(Show $show): self
    {
        if (!$this->shows->contains($show)) {
            $this->shows[] = $show;
            $show->addSeason($this);
        }

        return $this;
    }

    /**
     * @param Show $show
     * @return $this
     */
    public function removeShow(Show $show): self
    {
        if ($this->shows->removeElement($show)) {
            $show->removeSeason($this);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @return Collection|Election[]
     */
    public function getElections(): Collection
    {
        return $this->elections;
    }

    /**
     * @param Election $election
     * @return $this
     */
    public function addElection(Election $election): self
    {
        if (!$this->elections->contains($election)) {
            $this->elections[] = $election;
            $election->setSeason($this);
        }

        return $this;
    }

    /**
     * @param Election $election
     * @return $this
     */
    public function removeElection(Election $election): self
    {
        if ($this->elections->removeElement($election)) {
            // set the owning side to null (unless already changed)
            /** @noinspection NestedPositiveIfStatementsInspection */
            if ($election->getSeason() === $this) {
                $election->setSeason(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DiscordChannel[]
     */
    public function getDiscordChannels(): Collection
    {
        return $this->discordChannels;
    }

    /**
     * @param DiscordChannel $discordChannel
     * @return $this
     */
    public function addDiscordChannel(DiscordChannel $discordChannel): self
    {
        if (!$this->discordChannels->contains($discordChannel)) {
            $this->discordChannels[] = $discordChannel;
            $discordChannel->setSeason($this);
        }

        return $this;
    }

    /**
     * @param DiscordChannel $discordChannel
     * @return $this
     */
    public function removeDiscordChannel(DiscordChannel $discordChannel): self
    {
        if ($this->discordChannels->removeElement($discordChannel)) {
            // set the owning side to null (unless already changed)
            /** @noinspection NestedPositiveIfStatementsInspection */
            if ($discordChannel->getSeason() === $this) {
                $discordChannel->setSeason(null);
            }
        }

        return $this;
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'year' => $this->getYear(),
            'yearPart' => $this->getYearPart(),
            'rankOrder' => $this->getRankOrder(),
        ];
    }

}
