<?php

namespace App\Entity;

/**
 * Class UserPreferences
 * @package App\Entity
 */
final class UserPreferences
{
    private ?string $colorsMode;

    private ?string $allWatchesViewMode;

    public function __construct() {
        $this->colorsMode = 'os';
        $this->allWatchesViewMode = 'expanded';
    }

    public function setColorsMode(string $mode): void
    {
        $this->colorsMode = $mode;
    }

    /**
     * @return string
     */
    public function getColorsMode(): string
    {
        return $this->colorsMode ?? 'os';
    }

    public function toArray(): array
    {
        return [
            'colorsMode' => $this->colorsMode,
            'allWatchesViewMode' => $this->allWatchesViewMode,
        ];
    }

    /**
     * @return string
     */
    public function getAllWatchesViewMode(): string
    {
        return $this->allWatchesViewMode ?? 'expanded';
    }

    /**
     * @param string $allWatchesViewMode
     */
    public function setAllWatchesViewMode(string $allWatchesViewMode): void
    {
        $this->allWatchesViewMode = $allWatchesViewMode;
    }
}
