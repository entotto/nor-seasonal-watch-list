<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

namespace App\Controller;

use App\Repository\ActivityRepository;
use App\Repository\SeasonRepository;
use App\Repository\ShowRepository;
use App\Repository\ShowSeasonScoreRepository;
use App\Repository\UserRepository;
use App\Service\SelectedSeasonHelper;
use App\Service\SelectedSortHelper;
use Doctrine\DBAL\Exception;
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
     * @param SeasonRepository $seasonRepository
     * @param ShowRepository $showRepository
     * @param ShowSeasonScoreRepository $showSeasonScoreRepository
     * @param UserRepository $userRepository
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
        SeasonRepository $seasonRepository,
        ShowRepository $showRepository,
        ShowSeasonScoreRepository $showSeasonScoreRepository,
        UserRepository $userRepository,
        ActivityRepository $activityRepository,
        SelectedSeasonHelper $selectedSeasonHelper,
        SelectedSortHelper $selectedSortHelper
    ): Response {
        $selectedSeasonId = null;
        $seasons = $seasonRepository->getAllInRankOrder();
        $season = $selectedSeasonHelper->getSelectedSeason($request);
        $selectedSortName = $selectedSortHelper->getSelectedSort($request,'community_watch');
        $sortOptions = [
            'show_asc' => 'Show &or;',
            'show_desc' => 'Show &and;',
            'activity_desc' => 'Activity &or;',
            'activity_asc' => 'Activity &and;',
            'recommendations_desc' => 'Recommendations &or;',
            'recommendations_asc' => 'Recommendations &and;',
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
        $maxScore = 0;
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
                    $moodEmoji = 'emoji-heart-eyes-fill';
                } elseif ($moodAverageValue > 1) {
                    $moodEmoji = 'emoji-smile-fill';
                } elseif ($moodAverageValue > -1) {
                    $moodEmoji = 'emoji-neutral-fill';
                } else {
                    $moodEmoji = 'emoji-frown-fill';
                }
                $maxScore = max([
                    $maxScore,
                    $consolidatedShowScore['th8a_count'],
                    $consolidatedShowScore['highly_favorable_count'],
                    $consolidatedShowScore['favorable_count'],
                    $consolidatedShowScore['neutral_count'],
                    $consolidatedShowScore['unfavorable_count'],
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
                $scores = $showSeasonScoreRepository->findAllForSeasonAndShow($season, $show);
                $filteredScores = [];
                foreach ($scores as $score) {
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
                    'maxScore' => $maxScore,
                    'maxActivityCount' => $maxActivityCount,
                ];
            }
        }

        return $this->render('all_watch/index.html.twig', [
            'controller_name' => 'AllWatchController',
            'seasons' => $seasons,
            'selectedSeasonId' => $selectedSeasonId,
            'users' => $userKeys,
            'data' => $data,
            'total_columns' => 2 + count($users),
            'selectedSortName' => $selectedSortName,
            'sortOptions' => $sortOptions,
        ]);
    }
}
