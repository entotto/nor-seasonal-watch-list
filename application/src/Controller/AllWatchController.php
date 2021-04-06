<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

namespace App\Controller;

use App\Entity\ShowSeasonScore;
use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Repository\ScoreRepository;
use App\Repository\SeasonRepository;
use App\Repository\ShowRepository;
use App\Repository\ShowSeasonScoreRepository;
use App\Repository\UserRepository;
use App\Service\SelectedSeasonHelper;
use App\Service\SelectedSortHelper;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\u;

class AllWatchController extends AbstractController
{
    /**
     * @Route("/community/watch", name="all_watch_index", options={"expose"=true})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param SeasonRepository $seasonRepository
     * @param ShowRepository $showRepository
     * @param ShowSeasonScoreRepository $showSeasonScoreRepository
     * @param UserRepository $userRepository
     * @param ScoreRepository $scoreRepository
     * @param ActivityRepository $activityRepository
     * @param SelectedSeasonHelper $selectedSeasonHelper
     * @param SelectedSortHelper $selectedSortHelper
     * @return Response
     * @throws Exception
     * @throws NonUniqueResultException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function index(
        Request $request,
        EntityManagerInterface $em,
        SeasonRepository $seasonRepository,
        ShowRepository $showRepository,
        ShowSeasonScoreRepository $showSeasonScoreRepository,
        UserRepository $userRepository,
        ScoreRepository $scoreRepository,
        ActivityRepository $activityRepository,
        SelectedSeasonHelper $selectedSeasonHelper,
        SelectedSortHelper $selectedSortHelper
    ): Response {
        $selectedSeasonId = null;
        $seasons = $seasonRepository->getAllInRankOrder();
        $season = $selectedSeasonHelper->getSelectedSeason($request);
        $selectedSortName = $selectedSortHelper->getSelectedSort($request,'community_watch');
        $sortOptions = [
            'show_asc' => 'Show &#9660;',
            'show_desc' => 'Show &#9650;',
            'activity_desc' => 'Activity &#9660;',
            'activity_asc' => 'Activity &#9650;',
            'recommendations_desc' => 'Recommendations &#9660;',
            'recommendations_asc' => 'Recommendations &#9650;',
        ];
        $activities = $activityRepository->findAll();
        $activityValues = [];
        foreach ($activities as $activity) {
            $activityValues[$activity->getSlug()] = $activity->getValue();
        }
        $users = $userRepository->getAllSorted();
        $userKeys = [];
        foreach ($users as $user) {
            $userKeys[$user->getUsername()] = false;
        }
        $data = [];
        $maxScoreCount = 0;
        $maxActivityCount = 0;
        if ($season !== null) {
            $selectedSeasonId = $season->getId();
            $shows = $showRepository->getShowsForSeason($season, null, $selectedSortName);
            if ($selectedSortName !== 'show_asc' && $selectedSortName !== 'show_desc') {
                // When sorting by a calculated value (avg or sum in this case), Doctrine returns
                // an array of arrays, with each entry looking like this:
                //   [ 0 => $show, 'calculated_value' => "1.000" ]
                $actualShows = [];
                foreach ($shows as $showContainer) {
                    $actualShows[] = $showContainer[0];
                }
                $shows = $actualShows;
            }

            // Add in any missing individual score rows
            /** @var User $user */
            $user = $this->getUser();
            $defaultScore = $scoreRepository->getDefaultScore();
            $defaultActivity = $activityRepository->getDefaultActivity();
            foreach ($shows as $key => $show) {
                $score = $showSeasonScoreRepository->getForUserAndShowAndSeason(
                    $user,
                    $show,
                    $season
                );
                if ($score === null) {
                    $score = new ShowSeasonScore();
                    $score->setUser($user);
                    $score->setShow($show);
                    $score->setSeason($season);
                    $score->setScore($defaultScore);
                    $score->setActivity($defaultActivity);
                    $em->persist($score);
                    $em->flush();
                }
            }
            // End of adding missing score rows

            $consolidatedShowActivities = $showSeasonScoreRepository->getActivitiesForSeason($season);
            $keyedConsolidatedShowActivities = [];
            foreach ($consolidatedShowActivities as $consolidatedShowActivity) {
                $consolidatedShowActivity['total_count'] =
                    ($consolidatedShowActivity['watching_count'] * $activityValues['watching']) +
                    ($consolidatedShowActivity['ptw_count'] * $activityValues['ptw']);
                $maxActivityCount = max([
                    $maxActivityCount,
                    $consolidatedShowActivity['total_count']
                ]);
                $consolidatedShowActivity['activities_array'] = '[' .
                    ($consolidatedShowActivity['watching_count'] * $activityValues['watching']) . ',' .
                    ($consolidatedShowActivity['ptw_count'] * $activityValues['ptw']) . ']';
                $keyedConsolidatedShowActivities[$consolidatedShowActivity['show_id']] = $consolidatedShowActivity;
            }

            $consolidatedShowScores = $showSeasonScoreRepository->getScoresForSeason($season);
            $keyedConsolidatedShowScores = [];
            foreach ($consolidatedShowScores as $consolidatedShowScore) {
                $moodAverageValue = ($consolidatedShowScore['all_count'] > 0) ?
                    $consolidatedShowScore['score_total'] / $consolidatedShowScore['all_count'] : 0;
                if ($moodAverageValue > 5) {
//                    $moodEmoji = 'emoji-heart-eyes-fill';
                    $moodEmoji = <<<EOF
<i class="bi bi-circle-fill bi-x-upper-half position-absolute mood-emoji-heart-eyes-color"></i>
<i class="bi bi-circle-fill bi-x-lower-half position-absolute mood-emoji-dark-mouth-color"></i>
<i class="bi bi-emoji-heart-eyes-fill position-absolute mood-emoji-favorable-color"></i>
EOF;
                } elseif ($moodAverageValue > 1) {
                    // $moodEmoji = 'emoji-smile-fill';
                    $moodEmoji = <<<EOF
<i class="bi bi-circle-fill bi-x-shrunk-circle position-absolute mood-emoji-dark-mouth-color"></i>
<i class="bi bi-emoji-smile-fill position-absolute mood-emoji-favorable-color"></i>
EOF;
                } elseif ($moodAverageValue > -1) {
                    // $moodEmoji = 'emoji-neutral-fill';
                    $moodEmoji = <<<EOF
<i class="bi bi-circle-fill bi-x-shrunk-circle position-absolute mood-emoji-dark-mouth-color"></i>
<i class="bi bi-emoji-neutral-fill position-absolute mood-emoji-neutral-color"></i>
EOF;
                } else {
                    // $moodEmoji = 'emoji-frown-fill';
                    $moodEmoji = <<<EOF
<i class="bi bi-circle-fill bi-x-shrunk-circle position-absolute mood-emoji-light-mouth-color"></i>
<i class="bi bi-emoji-frown-fill position-absolute mood-emoji-unfavorable-color"></i>
EOF;
                }
                $maxScoreCount = max([
                    $maxScoreCount,
                    ($consolidatedShowScore['th8a_count'] +
                    $consolidatedShowScore['highly_favorable_count'] +
                    $consolidatedShowScore['favorable_count'] +
                    $consolidatedShowScore['neutral_count'] +
                    $consolidatedShowScore['unfavorable_count'])
                ]);
                $consolidatedShowScore['scores_array'] = '[' .
                    $consolidatedShowScore['th8a_count'] . ',' .
                    $consolidatedShowScore['highly_favorable_count'] . ',' .
                    $consolidatedShowScore['favorable_count'] . ',' .
                    $consolidatedShowScore['neutral_count'] . ',' .
                    $consolidatedShowScore['unfavorable_count'] .
                    ']';
                $consolidatedShowScore['mood_array'] = [
                    'mood_average_value' => $moodAverageValue,
                    'mood_emoji' => $moodEmoji,
                ];
                $keyedConsolidatedShowScores[$consolidatedShowScore['show_id']] = $consolidatedShowScore;
            }
            unset($consolidatedShowScores);
            foreach ($shows as $key => $show) {
                $showInfo = [
                    'id' => $show->getId(),
                    'title' => u($show->getAllTitles())->truncate(240, '...', false),
                    'shortTitle' => u($show->getJapaneseTitle())->truncate(100, '...', false),
                    'coverImage' => $show->getCoverImageLarge(),
                    'coverImageMedium' => $show->getCoverImageMedium(),
                    'anilistId' => $show->getAnilistId(),
                    'anilistShowUrl' => $show->getSiteUrl() ?: "https://anilist.co/anime/" . $show->getAnilistId(),
                    'malShowUrl' => $show->getMalId() ? "https://myanimelist.net/anime/" . $show->getMalId() : '',
                ];
                $scoreValues = $showSeasonScoreRepository->findAllForSeasonAndShow($season, $show, 'displayname');
                $filteredScores = [];
                foreach ($scoreValues as $scoreValue) {
                    $score = $scoreValue[0];
                    if ($score->getScore() !== null && $score->getScore()->getValue() !== 0) {
                        $userKeys[$score->getUser()->getUsername()] = true;
                    }
                    if (
                        ($score->getScore() && $score->getScore()->getSlug() !== 'none')
                        || ($score->getActivity() && $score->getActivity()->getSlug() !== 'none')
                    ) {
                        $filteredScores[] = $score;
                    }
                }
                $data[] = [
                    'show' => $showInfo,
                    'consolidatedActivities' => $keyedConsolidatedShowActivities[$show->getId()] ?? null,
                    'consolidatedScores' => $keyedConsolidatedShowScores[$show->getId()] ?? null,
                    'scores' => $filteredScores,
                    'scoreCount' => count($filteredScores),
                    'maxScoreCount' => $maxScoreCount,
                    'maxActivityCount' => $maxActivityCount,
                ];
            }
        }

        return $this->render('all_watch/index.html.twig', [
            'controller_name' => 'AllWatchController',
            'seasons' => $seasons,
            'selectedSeasonId' => $selectedSeasonId,
            'users' => $userKeys,
            'user' => $this->getUser(),
            'data' => $data,
            'total_columns' => 2 + count($users),
            'selectedSortName' => $selectedSortName,
            'sortOptions' => $sortOptions,
        ]);
    }
}
