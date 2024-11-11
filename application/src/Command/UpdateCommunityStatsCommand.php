<?php /** @noinspection UnknownInspectionInspection */

/** @noinspection PhpUnused */

namespace App\Command;

use App\Service\ScoreMood;
use App\Service\ScoreMoodHelper;
use App\Service\SwlApi;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MoodStats
{
    public array $activityDistributionBySlug = [];
    public array $scoreDistributionBySlug = [];
    public array $showDistributionByActivityVotes = [];
    public array $showDistributionByScoreVotes = [];

    public function incrementActivityCount(string $slug) {
        $this->activityDistributionBySlug[$slug] = ($this->activityDistributionBySlug[$slug] ?? 0) + 1;
    }

    public function incrementScoreCount(string $slug) {
        $this->scoreDistributionBySlug[$slug] = ($this->scoreDistributionBySlug[$slug] ?? 0) + 1;
    }

    public function incrementShowCountForActivityVotes(int $votes) {
        $this->showDistributionByActivityVotes[$votes] = ($this->showDistributionByActivityVotes[$votes] ?? 0) + 1;
    }

    public function incrementShowCountForScoreVotes(int $votes) {
        $this->showDistributionByScoreVotes[$votes] = ($this->showDistributionByScoreVotes[$votes] ?? 0) + 1;
    }
}

class CommunityStats
{
    public array $statsByMood = [];
    public array $showCountsByMood = [];
    public int $userCount = 0;

    public function getStatsForMood(ScoreMood $mood): MoodStats {
        if (!isset($this->statsByMood[$mood->value])) {
            $this->statsByMood[$mood->value] = new MoodStats();
        }
        return $this->statsByMood[$mood->value];
    }

    public function incrementShowCountsForMood(ScoreMood $mood) {
        $this->showCountsByMood[$mood->value] = ($this->showCountsByMood[$mood->value] ?? 0) + 1;
    }
}

class UpdateCommunityStatsCommand extends Command
{
    protected static $defaultName = 'data:update-community-stats';

    private ScoreMoodHelper $helper;
    private string $dataDir;
    private SwlApi $swlApi;

    public function __construct(
        string $dataDir,
        ScoreMoodHelper $helper,
        SwlApi $swlApi,
    ) {
        parent::__construct();
        $this->dataDir = $dataDir;
        $this->helper = $helper;
        $this->swlApi = $swlApi;
    }

    /** @noinspection ReturnTypeCanBeDeclaredInspection */
    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription('Update data/community_stats.json.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $communityStats = $this->computeCommunityStats();
            $outpuPath = $this->dataDir . '/community_stats.json';
            file_put_contents($outpuPath, json_encode($communityStats, JSON_PRETTY_PRINT));
            $io->info('Wrote ' . $outpuPath);
            return 0;
        } catch (Exception $e) {
            $io->error('An error occurred while crunching numbers.');
            $io->error($e->getMessage());
            return 1;
        }
    }

    private function fetchCommunityWatches(): array {
        $seasons = $this->swlApi->getSeasons();
        if (count($seasons) === 0) {
            throw new Exception('No seasons found');
        }
        return $this->swlApi->getSeasonsCommunityWatchesById($seasons[count($seasons) - 1]["id"]);
    }

    private function computeCommunityStats(): CommunityStats
    {
        $raw = $this->fetchCommunityWatches();
        $communityStats = new CommunityStats();
        $seenUsersById = [];
        foreach ($raw['shows'] as $show) {
            $mood = count($show['scores']) === 0 ? ScoreMood::None : $this->helper->scoreToMood($show['consolidatedRecommendations']['mood_average_value']);

            $communityStats->incrementShowCountsForMood($mood);

            if (count($show['scores']) === 0) {
                continue;
            }

            $moodStats = $communityStats->getStatsForMood($mood);
            $activityVotes = 0;
            $scoreVotes = 0;
            foreach ($show['scores'] as $score) {
                if ($score['activity']['slug'] !== 'none') {
                    $moodStats->incrementActivityCount($score['activity']['slug']);
                    $activityVotes += 1;
                }
                if ($score['recommendation']['slug'] !== 'none') {
                    $moodStats->incrementScoreCount($score['recommendation']['slug']);
                    $scoreVotes += 1;
                }
                $seenUsersById[$score['user']['id']] = true;
            }
            $moodStats->incrementShowCountForActivityVotes($activityVotes);
            $moodStats->incrementShowCountForScoreVotes($scoreVotes);
        }
        $communityStats->userCount = count($seenUsersById);
        return $communityStats;
    }
}
