<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpUnusedAliasInspection */

namespace App\Entity;

use App\Repository\ShowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ShowRepository::class)
 * @ORM\Table(name="anime_show")
 * @UniqueEntity(fields="anilist_id", message="That Anilist ID is already taken")
 */
class Show
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $japaneseTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $englishTitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $fullJapaneseTitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $fullEnglishTitle;

    /**
     * @ORM\ManyToMany(targetEntity=Season::class, inversedBy="shows")
     * @OrderBy({"rankOrder" = "ASC"})
     */
    private Collection $seasons;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $anilistId;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $hashtag;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $coverImageMedium;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $coverImageLarge;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $siteUrl;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $synonyms;

    /**
     * @var bool|null
     * @ORM\Column(name="exclude_from_elections", type="boolean", nullable=true)
     */
    private ?bool $excludeFromElections;

    /**
     * @var DiscordChannel|null
     * @ORM\OneToOne(targetEntity=DiscordChannel::class, mappedBy="animeShow", cascade={"persist", "remove"})
     * @JoinColumn(nullable=true)
     */
    private ?DiscordChannel $discordChannel;

    public function __construct()
    {
        $this->seasons = new ArrayCollection();
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
    public function getJapaneseTitle(): ?string
    {
        return $this->japaneseTitle;
    }

    /**
     * @param string|null $japaneseTitle
     * @return $this
     */
    public function setJapaneseTitle(?string $japaneseTitle): self
    {
        $this->japaneseTitle = $japaneseTitle;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEnglishTitle(): ?string
    {
        return $this->englishTitle;
    }

    /**
     * @param string|null $englishTitle
     * @return $this
     */
    public function setEnglishTitle(?string $englishTitle): self
    {
        $this->englishTitle = $englishTitle;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFullJapaneseTitle(): ?string
    {
        return $this->fullJapaneseTitle;
    }

    /**
     * @param string|null $fullJapaneseTitle
     * @return $this
     */
    public function setFullJapaneseTitle(?string $fullJapaneseTitle): self
    {
        $this->fullJapaneseTitle = $fullJapaneseTitle;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFullEnglishTitle(): ?string
    {
        return $this->fullEnglishTitle;
    }

    /**
     * @param string|null $fullEnglishTitle
     * @return $this
     */
    public function setFullEnglishTitle(?string $fullEnglishTitle): self
    {
        $this->fullEnglishTitle = $fullEnglishTitle;

        return $this;
    }

    public function getAllTitles(): ?string
    {
        $result = [];
        if (!empty($this->japaneseTitle)) {
            $result[] = $this->japaneseTitle;
        }
        if (!empty($this->fullJapaneseTitle)) {
            $result[] = $this->fullJapaneseTitle;
        }
        if (!empty($this->englishTitle)) {
            $result[] = $this->englishTitle;
        }
        return empty($result) ? null : implode('<br>', $result);
    }

    public function getAllShortTitles(): ?string
    {
        $result = [];
        if (!empty($this->japaneseTitle)) {
            $result[] = $this->japaneseTitle;
        }
        if (!empty($this->fullJapaneseTitle)) {
            $result[] = $this->fullJapaneseTitle;
        }
        return empty($result) ? null : implode(' / ', $result);
    }

    /**
     * @return Collection|Season[]
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
    }

    /**
     * @param Season $season
     * @return $this
     */
    public function addSeason(Season $season): self
    {
        if (!$this->seasons->contains($season)) {
            $this->seasons[] = $season;
        }

        return $this;
    }

    /**
     * @param Season $season
     * @return $this
     */
    public function removeSeason(Season $season): self
    {
        $this->seasons->removeElement($season);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAnilistId(): ?string
    {
        return $this->anilistId;
    }

    /**
     * @param string|null $anilistId
     * @return $this
     */
    public function setAnilistId(?string $anilistId): self
    {
        $this->anilistId = $anilistId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getHashtag(): ?string
    {
        return $this->hashtag;
    }

    /**
     * @param string|null $hashtag
     */
    public function setHashtag(?string $hashtag): void
    {
        $this->hashtag = $hashtag;
    }

    /**
     * @return string|null
     */
    public function getCoverImageMedium(): ?string
    {
        return $this->coverImageMedium;
    }

    /**
     * @param string|null $coverImageMedium
     */
    public function setCoverImageMedium(?string $coverImageMedium): void
    {
        $this->coverImageMedium = $coverImageMedium;
    }

    /**
     * @return string|null
     */
    public function getCoverImageLarge(): ?string
    {
        return $this->coverImageLarge;
    }

    /**
     * @param string|null $coverImageLarge
     */
    public function setCoverImageLarge(?string $coverImageLarge): void
    {
        $this->coverImageLarge = $coverImageLarge;
    }

    /**
     * @return string|null
     */
    public function getSiteUrl(): ?string
    {
        return $this->siteUrl;
    }

    /**
     * @param string|null $siteUrl
     */
    public function setSiteUrl(?string $siteUrl): void
    {
        $this->siteUrl = $siteUrl;
    }

    /**
     * @return string|null
     */
    public function getSynonyms(): ?string
    {
        return $this->synonyms;
    }

    /**
     * @param string|null $synonyms
     */
    public function setSynonyms(?string $synonyms): void
    {
        $this->synonyms = $synonyms;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getJapaneseTitle() . ' (' . $this->getEnglishTitle() . ')';
    }

    /**
     * @return DiscordChannel|null
     */
    public function getDiscordChannel(): ?DiscordChannel
    {
        return $this->discordChannel;
    }

    /**
     * @param DiscordChannel|null $discordChannel
     * @return $this
     */
    public function setDiscordChannel(?DiscordChannel $discordChannel): self
    {
        // set the owning side of the relation if necessary
        if ($discordChannel && $discordChannel->getShow() !== $this) {
            $discordChannel->setShow($this);
        }

        $this->discordChannel = $discordChannel;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getExcludeFromElections(): ?bool
    {
        return $this->excludeFromElections;
    }

    /**
     * @param bool|null $excludeFromElections
     */
    public function setExcludeFromElections(?bool $excludeFromElections): void
    {
        $this->excludeFromElections = $excludeFromElections;
    }
}
