<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Score;
use App\Entity\Season;
use App\Entity\Show;
use App\Entity\ShowSeasonScore;
use App\Entity\User;
use App\Service\ScoreMood;
use App\Service\ScoreMoodHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

function sampleElement(array $weightedChoices, $default = null) {
    if (count($weightedChoices) === 0) {
        return $default;
    }
    $weights = array_values($weightedChoices);
    $totalSamples = array_sum($weights);
    $chosenSample = random_int(0, $totalSamples - 1);
    $accumulatedSamples = 0;
    foreach ($weightedChoices as $choice => $sampleCount) {
        $accumulatedSamples += $sampleCount;
        if ($chosenSample <= $accumulatedSamples) {
            return $choice;
        }
    }
    return $weightedChoices[count($weightedChoices) - 1];
}

class ScoreFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array {
         return ['scores'];
    }

    public function __construct(string $dataDir, ScoreMoodHelper $scoreMoodHelper) {
        $this->dataDir = $dataDir;
        $this->scoreMoodHelper = $scoreMoodHelper;
    }

    private function readCommunityStats() {
        return json_decode(file_get_contents($this->dataDir. '/community_stats.json'), true);
    }

    private function pickTargetMood(array $communityStats, array $currentShowCountsByMood): string {
        // Make sure each mood has at least one show with that mood.
        foreach ($currentShowCountsByMood as $moodValue => $showCount) {
            if ($showCount === 0) {
                return $moodValue;
            }
        }
        // Otherwise, pick a mood according to sample distribution.
        return sampleElement($communityStats['showCountsByMood']);
    }

    private function generateActivityVotes(int $userCount, string $targetMood, array $communityStats): array {
        $currentVotes = array_fill(0, $userCount, null);

        $numberOfVotesToGenerate = ceil($userCount * sampleElement(
            $communityStats['statsByMood'][$targetMood]['showDistributionByActivityVotes'] ?? array(),
            0,
        ) / $communityStats['userCount']);
        $activityDistributionBySlug = $communityStats['statsByMood'][$targetMood]['activityDistributionBySlug'] ?? [];

        foreach (range(0, $numberOfVotesToGenerate) as $i) {
            $currentVotes[$i] = sampleElement($activityDistributionBySlug);
        }

        shuffle($currentVotes);
        return $currentVotes;
    }

    private function generateScoreVotes(int $userCount, string $targetMood, array $communityStats, array $scoreOptionsBySlug): array {
        $currentVotes = array_fill(0, $userCount, null);

        if ($targetMood === ScoreMood::None->value) {
            return $currentVotes;
        }

        $numberOfVotesToGenerate = ceil($userCount * sampleElement(
            $communityStats['statsByMood'][$targetMood]['showDistributionByScoreVotes'] ?? array(),
            0,
        ) / $communityStats['userCount']);
        $scoreDistributionBySlug = $communityStats['statsByMood'][$targetMood]['scoreDistributionBySlug'] ?? [];

        // Generate votes according to sample distribution.
        foreach (range(0, $numberOfVotesToGenerate) as $i) {
            $currentVotes[$i] = sampleElement($scoreDistributionBySlug);
        }

        // Determine score value to shoot for based on the thresholds
        // defined in ScoreMoodHelper.
        $targetScore = match($targetMood) {
            ScoreMood::HeartEyes->value => 7,
            ScoreMood::Smile->value => 3,
            ScoreMood::Neutral->value => 0,
            ScoreMood::Frown->value => -3,
        };

        // Make just one pass to try to adjust votes so that the overall mood
        // matches the target mood.

        // Even when adjusting votes, we pick from the sample distribution,
        // but first, make sure we aren't left with no adjustment choices.
        $scoreDistributionForCorrection = $scoreDistributionBySlug;
        foreach ($scoreOptionsBySlug as $slug => $score) {
            if ($score->getValue() === 0) {
                continue;
            }
            if (!array_key_exists($slug, $scoreDistributionForCorrection)) {
                $scoreDistributionForCorrection[$slug] = 1;
            }
        }
        foreach (range(0, $numberOfVotesToGenerate) as $i) {
            $currentScore = $this->computeAverageScore($currentVotes, $scoreOptionsBySlug);
            $currentMood = $this->scoreMoodHelper->scoreToMood($currentScore);
            if ($currentMood->value === $targetMood) {
                break;
            }
            $filterFunction = match(true) {
                // If current score is too high, pick from score options that'll decrease the score.
                $currentScore >= $targetScore => fn ($slug) => $scoreOptionsBySlug[$slug]->getValue() < $targetScore,
                // If current score is too low, pick from score options that'll increase the score.
                $currentScore < $targetScore => fn ($slug) => $scoreOptionsBySlug[$slug]->getValue() > $targetScore,
            };
            $filteredDistribution = array_filter($scoreDistributionForCorrection, $filterFunction, ARRAY_FILTER_USE_KEY);
            $currentVotes[$i] = sampleElement($filteredDistribution);
        }

        shuffle($currentVotes);
        return $currentVotes;
    }

    private function computeAverageScore(array $scoreSlugs, array $scoreOptionsBySlug): float {
        if (count($scoreSlugs) === 0) {
            return 0;
        }
        $votes = array_sum(array_map(fn($slug) => $slug === null ? 0 : 1, $scoreSlugs));
        if ($votes === 0) {
            return 0;
        }
        $scoreTotal = array_sum(array_map(fn($slug) => $slug === null ? 0 : $scoreOptionsBySlug[$slug]->getValue(), $scoreSlugs));
        return $scoreTotal / $votes;
    }

    public function load(ObjectManager $manager) {
        $season = $manager->getRepository(Season::class)->getMostRecentSeason();
        if ($season === null) {
            throw new \Exception('No season in the database. Please add one through the web UI.');
        }

        $activities = $manager->getRepository(Activity::class)->findAllInRankOrder();
        $activityOptionsBySlug = array_combine(array_map(fn($x) => $x->getSlug(), $activities), $activities);
        $scores = $manager->getRepository(Score::class)->findAllInRankOrder();
        $scoreOptionsBySlug = array_combine(array_map(fn($x) => $x->getSlug(), $scores), $scores);

        $showSeasonScoreRepo = $manager->getRepository(ShowSeasonScore::class);
        $showRepo = $manager->getRepository(Show::class);
        $userRepo = $manager->getRepository(User::class);

        $users = $userRepo->findAll();
        $communityStats = $this->readCommunityStats();

        $currentShowCountsByMood = array_combine(array_column(ScoreMood::cases(), 'value'), array_fill(0, count(ScoreMood::cases()), 0));

        foreach ($showRepo->findAll() as $show) {
            error_log("==== Populating show: ". $show->getEnglishTitle());

            $targetMood = $this->pickTargetMood($communityStats, $currentShowCountsByMood);
            $currentShowCountsByMood[$targetMood] += 1;
            error_log("Target mood: ". $targetMood);

            $activityVotes = $this->generateActivityVotes(count($users), $targetMood, $communityStats);
            error_log("Activity votes: ". implode(", ", $activityVotes));

            $scoreVotes = $this->generateScoreVotes(count($users), $targetMood, $communityStats, $scoreOptionsBySlug);
            error_log("Score votes: ". implode(", ", $scoreVotes));

            $finalScore = $this->computeAverageScore($scoreVotes, $scoreOptionsBySlug);
            $finalMood = $this->scoreMoodHelper->scoreToMood($finalScore);
            error_log('Final score: '. $finalScore);
            error_log('Final mood: '. $finalMood->value);

            foreach ($users as $i => $user) {
                $showSeasonScore = $showSeasonScoreRepo->getForUserAndShowAndSeason($user, $show, $season);
                if ($showSeasonScore === null) {
                    $showSeasonScore = new ShowSeasonScore();
                    $showSeasonScore->setUser($user);
                    $showSeasonScore->setShow($show);
                    $showSeasonScore->setSeason($season);
                    $manager->persist($showSeasonScore);
                }
                $showSeasonScore->setActivity($activityVotes[$i] === null ? null : $activityOptionsBySlug[$activityVotes[$i]]);
                $showSeasonScore->setScore($scoreVotes[$i] === null ? null : $scoreOptionsBySlug[$scoreVotes[$i]]);
            }
        }
        $manager->flush();
    }
}
