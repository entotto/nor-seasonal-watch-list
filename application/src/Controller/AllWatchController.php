<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

namespace App\Controller;

use App\Repository\SeasonRepository;
use App\Repository\ShowRepository;
use App\Repository\ShowSeasonScoreRepository;
use App\Repository\UserRepository;
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
     * @Route("/all/watch", name="all_watch_index", options={"expose"=true})
     * @param Request $request
     * @param SeasonRepository $seasonRepository
     * @param ShowRepository $showRepository
     * @param ShowSeasonScoreRepository $showSeasonScoreRepository
     * @param UserRepository $userRepository
     * @return Response
     * @throws NonUniqueResultException
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function index(
        Request $request,
        SeasonRepository $seasonRepository,
        ShowRepository $showRepository,
        ShowSeasonScoreRepository $showSeasonScoreRepository,
        UserRepository $userRepository
    ): Response {
        $seasons = $seasonRepository->getAllInRankOrder();
        $selectedSeasonId = $request->get('season');

//        /** @var User $user */
//        $user = $this->getUser();
//        $data = [];
        if ($selectedSeasonId === null) {
            $season = $seasonRepository->getSeasonForDate();
            if ($season === null) {
                $season = $seasonRepository->getFirstSeason();
            }
        } else {
            $season = $seasonRepository->find($selectedSeasonId);
        }
        $users = $userRepository->getAllSorted();
        $userKeys = [];
        foreach ($users as $user) {
            $userKeys[$user->getUsername()] = false;
        }
        $data = [];
        $maxChartTick = 0;
        if ($season !== null) {
            $selectedSeasonId = $season->getId();
            $shows = $showRepository->getShowsForSeason($season);
            $consolidatedShowScores = $showSeasonScoreRepository->getCountsForSeason($season);
            $keyedConsolidatedShowScores = [];
            foreach ($consolidatedShowScores as $consolidatedShowScore) {
                $maxChartTick = max([
                    $maxChartTick,
                    $consolidatedShowScore['th8a_count'],
                    $consolidatedShowScore['suggested_count'],
                    $consolidatedShowScore['watching_count'],
                    $consolidatedShowScore['ptw_count'],
                    $consolidatedShowScore['dropped_count'],
                    $consolidatedShowScore['disliked_count'],
                ]);
                $consolidatedShowScore['scores_array'] = '[' .
                    $consolidatedShowScore['th8a_count'] . ',' .
                    $consolidatedShowScore['suggested_count'] . ',' .
                    $consolidatedShowScore['watching_count'] . ',' .
                    $consolidatedShowScore['ptw_count'] . ',' .
                    $consolidatedShowScore['dropped_count'] . ',' .
                    $consolidatedShowScore['disliked_count'] . ']';
                $keyedConsolidatedShowScores[$consolidatedShowScore['show_id']] = $consolidatedShowScore;
            }
            unset($consolidatedShowScores);
            foreach ($shows as $key => $show) {
                $showInfo = [
                    'id' => $show->getId(),
                    'japaneseTitle' => u($show->getJapaneseTitle())->truncate(40, '...', false),
                    'englishTitle' => u($show->getEnglishTitle())->truncate(40, '...', false),
                    'fullJapaneseTitle' => u($show->getFullJapaneseTitle())->truncate(40, '...', false),
                    'coverImage' => $show->getCoverImageMedium(),
                ];
                $scores = $showSeasonScoreRepository->findAllForSeasonAndShow($season, $show);
                foreach ($scores as $score) {
                    if ($score->getScore() !== null && $score->getScore()->getValue() !== 0) {
                        $userKeys[$score->getUser()->getUsername()] = true;
                    }
                }
                $data[] = [
                    'show' => $showInfo,
                    'consolidatedScores' => $keyedConsolidatedShowScores[$show->getId()] ?? null,
                    'scores' => $scores,
                    'maxChartTick' => $maxChartTick + 1,
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
        ]);
    }
}
