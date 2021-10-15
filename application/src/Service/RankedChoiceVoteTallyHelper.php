<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Show;
use App\Entity\View\RankedChoiceVoteTally;
use CondorcetPHP\Condorcet\Candidate;
use CondorcetPHP\Condorcet\Election as CondorcetElection;
use CondorcetPHP\Condorcet\Throwable\CondorcetException;

final class RankedChoiceVoteTallyHelper
{
    // public const VOTING_METHOD = 'Schulze';
    public const VOTING_METHOD = 'Ranked Pairs Winning';


    /**
     * @param array $votesInfo
     * @param Show[] $shows
     * @return RankedChoiceVoteTally[]
     * @throws CondorcetException
     */
    public function getTallies(array $votesInfo, array $shows): array
    {
        $election = new CondorcetElection();
        $candidates = [];
        $showsById = [];
        $rankingsByUser = [];
        foreach ($shows as $show) {
            $showId = $show->getId();
            $showsById[(string)$showId] = $show;
            $candidates[$showId] = $election->addCandidate((string)$showId);
        }
        foreach ($votesInfo as $voteInfo) {
            $userId = $voteInfo['user_id'];
            $showId = $voteInfo['show_id'];
            $rank = $voteInfo['rank_choice'];
            if (!isset($rankingsByUser[$voteInfo['user_id']])) {
                $rankingsByUser[$voteInfo['user_id']] = [];
            }
            if (isset($rankingsByUser[$userId][$rank])) {
                if (is_array($rankingsByUser[$userId][$rank])) {
                    $rankingsByUser[$userId][$rank][] = $candidates[$showId];
                } else {
                    $existingCandidate = $rankingsByUser[$userId][$rank];
                    $rankingsByUser[$userId][$rank] = [$existingCandidate, $candidates[$showId]];
                }
            } else {
                $rankingsByUser[$userId][$rank] = $candidates[$showId];
            }
        }

        foreach ($rankingsByUser as $rankingsBySingleUser) {
            ksort($rankingsBySingleUser);
            $election->addVote($rankingsBySingleUser);
        }

        $electionResults = $election->getResult(self::VOTING_METHOD);
        $electionResultsAsArray = $electionResults->getResultAsArray();
        $electionStats = $electionResults->getStats();
        $electionTally = $electionStats['tally'][0];

        $voteTallies = [];
        $currentRank = 1;
        foreach ($electionResultsAsArray as $result) {
            if (is_array($result)) {
                $sharedRank = $currentRank;
                foreach ($result as $candidate) {
                    $voteTallies[] = $this->getVoteTallyFromCandidate($showsById, $candidate, $sharedRank, $electionTally);
                    $currentRank++;
                }
            } else {
                $voteTallies[] = $this->getVoteTallyFromCandidate($showsById, $result, $currentRank, $electionTally);
                $currentRank++;
            }
        }

        return $voteTallies;
    }

    /**
     * @param array $showsById
     * @param Candidate $candidate
     * @param int $rank
     * @param array $electionTally
     * @return RankedChoiceVoteTally
     */
    private function getVoteTallyFromCandidate(
        array $showsById,
        Candidate $candidate,
        int $rank,
        array $electionTally
    ): RankedChoiceVoteTally {
        $voteTally = new RankedChoiceVoteTally();
        $show = $showsById[$candidate->getName()];
        $voteTally->setShowCombinedTitle($show->getVoteStyleTitles());
        $voteTally->setFinalRank($rank);
        $relatedShowNames = [];
        foreach ($show->getRelatedShows() as $relatedShow) {
            $relatedShowNames[] = $relatedShow->getVoteStyleTitles();
        }
        if (!empty($relatedShowNames)) {
            $voteTally->setRelatedShowNames($relatedShowNames);
        }
        $voteTally->setId($rank);
        $voteTally->setShowId($show->getId());
        $voteTally->setShowEnglishTitle($show->getEnglishTitle());
        $voteTally->setShowJapaneseTitle($show->getJapaneseTitle());
        $voteTally->setShowFullJapaneseTitle($show->getFullJapaneseTitle());

        $details = [];
        foreach($electionTally as $tally) {
            if (isset($showsById[$tally['to']]) && (int)$tally['from'] === $show->getId()) {
                if ($tally['win']) {
                    $details[] = sprintf(
                        'Won over <i>%s</i> by %d voter%s',
                        $showsById[$tally['to']]->getEnglishTitle(),
                        $tally['margin'],
                        $tally['margin'] === 1 ? '' : 's'
                    );
                } else {
                    $details[] = sprintf(
                        'Lost to <i>%s</i> by %d voter%s',
                        $showsById[$tally['to']]->getEnglishTitle(),
                        $tally['margin'],
                        $tally['margin'] === 1 ? '' : 's'
                    );
                }
            }
            if (isset($showsById[$tally['to']]) && (int)$tally['to'] === $show->getId()) {
                if ($tally['win']) {
                    $details[] = sprintf(
                        'Lost to <i>%s</i> by %d voter%s',
                        $showsById[$tally['from']]->getEnglishTitle(),
                        $tally['margin'],
                        $tally['margin'] === 1 ? '' : 's'
                    );
                } else {
                    $details[] = sprintf(
                        'Won over <i>%s</i> by %d voter%s',
                        $showsById[$tally['from']]->getEnglishTitle(),
                        $tally['margin'],
                        $tally['margin'] === 1 ? '' : 's'
                    );
                }
            }
        }
        if (!empty($details)) {
            $voteTally->setRankStats(implode('<br />', $details));
        }
        return $voteTally;
    }
}
