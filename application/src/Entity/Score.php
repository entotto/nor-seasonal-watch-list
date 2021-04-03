<?php /** @noinspection DuplicatedCode */
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\ScoreRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ScoreRepository::class)
 */
class Score
{
    /**
     * @var int|null
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    private string $nickname;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private int $rankOrder;

    /**
     * @var float|null
     * @ORM\Column(type="decimal", precision=10, scale=3, nullable=true)
     */
    private ?float $value;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    private string $colorValue;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $icon;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $slug;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? '';
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
     * @return int
     */
    public function getRankOrder(): int
    {
        return $this->rankOrder ?? 0;
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
     * @return float|null
     */
    public function getValue(): ?float
    {
        return $this->value;
    }

    /**
     * @param float|null $value
     * @return $this
     */
    public function setValue(?float $value): self
    {
        $this->value = $value;

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
     * @return string
     */
    public function getNickname(): string
    {
        return $this->nickname ?? '';
    }

    /**
     * @param string $nickname
     */
    public function setNickname(string $nickname): void
    {
        $this->nickname = $nickname;
    }

    /**
     * @return string
     */
    public function getColorValue(): string
    {
        return $this->colorValue ?? '';
    }

    /**
     * @param string $colorValue
     */
    public function setColorValue(string $colorValue): void
    {
        $this->colorValue = $colorValue;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon ?? '';
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug ?? '';
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
