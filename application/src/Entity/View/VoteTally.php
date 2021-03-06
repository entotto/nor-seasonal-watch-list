<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

namespace App\Entity\View;

class VoteTally
{
    /**
     * @var int $id
     */
    private int $id;

    /**
     * @var string $showJapaneseTitle
     */
    private string $showJapaneseTitle;

    /**
     * @var string $showEnglishTitle
     */
    private string $showEnglishTitle;

    /**
     * @var string $showFullJapaneseTitle
     */
    private string $showFullJapaneseTitle;

    /**
     * @var int $showId
     */
    private int $showId;

    /**
     * @var int $voteCount
     */
    private int $voteCount;

    /**
     * @var float $votePercentOfTotal
     */
    private float $votePercentOfTotal;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getShowJapaneseTitle(): string
    {
        return $this->showJapaneseTitle;
    }

    /**
     * @param string $showJapaneseTitle
     */
    public function setShowJapaneseTitle(string $showJapaneseTitle): void
    {
        $this->showJapaneseTitle = $showJapaneseTitle;
    }

    /**
     * @return string
     */
    public function getShowEnglishTitle(): string
    {
        return $this->showEnglishTitle;
    }

    /**
     * @param string $showEnglishTitle
     */
    public function setShowEnglishTitle(string $showEnglishTitle): void
    {
        $this->showEnglishTitle = $showEnglishTitle;
    }

    /**
     * @return string
     */
    public function getShowFullJapaneseTitle(): string
    {
        return $this->showFullJapaneseTitle;
    }

    /**
     * @param string $showFullJapaneseTitle
     */
    public function setShowFullJapaneseTitle(string $showFullJapaneseTitle): void
    {
        $this->showFullJapaneseTitle = $showFullJapaneseTitle;
    }

    /**
     * @return int
     */
    public function getShowId(): int
    {
        return $this->showId;
    }

    /**
     * @param int $showId
     */
    public function setShowId(int $showId): void
    {
        $this->showId = $showId;
    }

    /**
     * @return int
     */
    public function getVoteCount(): int
    {
        return $this->voteCount;
    }

    /**
     * @param int $voteCount
     */
    public function setVoteCount(int $voteCount): void
    {
        $this->voteCount = $voteCount;
    }

    /**
     * @return float
     */
    public function getVotePercentOfTotal(): float
    {
        return $this->votePercentOfTotal;
    }

    /**
     * @param float $votePercentOfTotal
     */
    public function setVotePercentOfTotal(float $votePercentOfTotal): void
    {
        $this->votePercentOfTotal = $votePercentOfTotal;
    }
}
