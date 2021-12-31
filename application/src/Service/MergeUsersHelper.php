<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ShowSeasonScore;
use App\Entity\User;
use App\Repository\ElectionVoteRepository;
use App\Repository\ShowSeasonScoreRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MergeUsersHelper
{
    private UserRepository $userRepository;
    private ElectionVoteRepository $electionVoteRepository;
    private EntityManagerInterface $em;
    private ShowSeasonScoreRepository $showSeasonScoreRepository;
    private bool $deleteOld;
    private array $changedScoreIds;

    /**
     * @param EntityManagerInterface $em
     * @param UserRepository $userRepository
     * @param ElectionVoteRepository $electionVoteRepository
     * @param ShowSeasonScoreRepository $showSeasonScoreRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        ElectionVoteRepository $electionVoteRepository,
        ShowSeasonScoreRepository $showSeasonScoreRepository
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->electionVoteRepository = $electionVoteRepository;
        $this->showSeasonScoreRepository = $showSeasonScoreRepository;
        $this->changedScoreIds = [];
    }

    public function merge(int $primaryUserId, bool $deleteOld, SymfonyStyle $io): void
    {
        $this->deleteOld = $deleteOld;
        $primaryAccount = $this->userRepository->find($primaryUserId);
        if ($primaryAccount === null) {
            $io->error("ID is invalid, cannot complete.");
            return;
        }

        $allAccounts = $this->userRepository->findAllByDiscordId($primaryAccount->getDiscordId());
        foreach ($allAccounts as $account) {
            if ($account->getId() === $primaryAccount->getId()) {
                continue;
            }
            $this->mergeUser($account, $primaryAccount);
        }
    }

    private function mergeUser(User $originalUser, User $newUser): void
    {
        $votes = $this->electionVoteRepository->getAllForUser($originalUser);
        foreach ($votes as $vote) {
            $vote->setUser($newUser);
            $this->em->persist($vote);
        }
        $this->em->flush();

        $originalScores = $this->showSeasonScoreRepository->findAllForUser($originalUser);
        $newScores = $this->showSeasonScoreRepository->findAllForUser($newUser);
        foreach($originalScores as $originalScore) {
            $foundNewScore = false;
            foreach ($newScores as $newScore) {
                /** @noinspection NullPointerExceptionInspection */
                if (
                    $originalScore->getShow()->getId() === $newScore->getShow()->getId() &&
                    $originalScore->getSeason()->getId() === $newScore->getSeason()->getId()
                ) {
                    $foundNewScore = true;

                    // If a new score exists with any values other than 'none' for score or activity, leave
                    // it alone and allow it to supersede the old score.
                    // However, multiple prior users may have scored the same show. If a new score is non-default
                    // because it was changed by an earlier score, allow following non-default original scores to
                    // still change the new score.

                    $newScoreHasAlreadyChanged = isset($this->changedScoreIds[$newScore->getId()]);
                    /** @noinspection NullPointerExceptionInspection */
                    $newScoreIsDefault = $newScore->getScore()->getSlug() === 'none' && $newScore->getActivity()->getSlug() === 'none';
                    /** @noinspection NullPointerExceptionInspection */
                    $originalScoreIsNotDefault = $originalScore->getScore()->getSlug() !== 'none' || $originalScore->getActivity()->getSlug() !== 'none';
                    if (
                        $newScoreIsDefault ||
                        ($newScoreHasAlreadyChanged && $originalScoreIsNotDefault)
                    ) {
                        $newScore->setScore($originalScore->getScore());
                        $newScore->setActivity($originalScore->getActivity());
                        $this->em->persist($newScore);
                        $this->changedScoreIds[$newScore->getId()] = true;
                    }
                }
            }
            if (!$foundNewScore) {
                $score = new ShowSeasonScore();
                $score->setShow($originalScore->getShow());
                $score->setSeason($originalScore->getSeason());
                $score->setUser($newUser);
                $score->setScore($originalScore->getScore());
                $score->setActivity($originalScore->getActivity());
                $this->em->persist($score);
            }
            if ($this->deleteOld) {
                $this->em->remove($originalScore);
            }
        }

        if ($this->deleteOld) {
            $this->em->remove($originalUser);
        }

        $this->em->flush();
    }
}
