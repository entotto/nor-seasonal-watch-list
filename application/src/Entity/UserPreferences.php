<?php

namespace App\Entity;

final class UserPreferences
{
    private ?string $colorsMode;

    public function __construct() {
        $this->colorsMode = 'dark';
    }

    /**
     * @return string
     */
    public function getColorsMode(): string
    {
        return $this->colorsMode ?? 'os';
    }

}
