<?php

namespace App\Entity;

/**
 * Class UserPreferences
 * @package App\Entity
 */
final class UserPreferences
{
    private ?string $colorsMode;

    public function __construct() {
        $this->colorsMode = 'os';
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
            'colorsMode' => $this->colorsMode
        ];
    }
}
