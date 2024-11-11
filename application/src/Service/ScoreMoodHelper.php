<?php

declare(strict_types=1);

namespace App\Service;

enum ScoreMood: string
{
    case HeartEyes = 'heart-eyes';
    case Smile = 'smile';
    case Neutral = 'neutral';
    case Frown = 'frown';
    case None = 'none';
}

final class ScoreMoodHelper
{
    /**
     * @param int $score
     */
    public function scoreToMood(float $score): ScoreMood {
        if ($score > 5) {
            return ScoreMood::HeartEyes;
        } else if ($score > 1) {
            return ScoreMood::Smile;
        } else if ($score > -1) {
            return ScoreMood::Neutral;
        }
        return ScoreMood::Frown;
    }
}
