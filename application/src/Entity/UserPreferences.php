<?php

namespace App\Entity;

final class UserPreferences
{
    private bool $darkMode;

    public function __construct() {
        $this->darkMode = false;
    }

    /**
     * @return bool
     */
    public function isDarkMode(): bool {
        return $this->darkMode;
    }

}
