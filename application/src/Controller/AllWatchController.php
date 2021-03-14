<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

namespace App\Controller;

use App\Repository\SeasonRepository;
use App\Repository\ShowRepository;
use App\Repository\ShowSeasonScoreRepository;
use App\Repository\UserRepository;
use App\Service\SelectedSeasonHelper;
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
     * @param SelectedSeasonHelper $selectedSeasonHelper
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
        SelectedSeasonHelper $selectedSeasonHelper
    ): Response {
        $selectedSeasonId = null;
        $seasons = $seasonRepository->getAllInRankOrder();
        $season = $selectedSeasonHelper->getSelectedSeason($request);
        $users = $userRepository->getAllSorted();
        $userKeys = [];
        foreach ($users as $user) {
            $userKeys[$user->getUsername()] = false;
        }
        $data = [];
        $maxScore = 0;
        if ($season !== null) {
            $selectedSeasonId = $season->getId();
            $shows = $showRepository->getShowsForSeason($season);
            $consolidatedShowScores = $showSeasonScoreRepository->getScoresForSeason($season);
            $keyedConsolidatedShowScores = [];
            foreach ($consolidatedShowScores as $consolidatedShowScore) {
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
                    $consolidatedShowScore['unfavorable_count'] . ']';
                $keyedConsolidatedShowScores[$consolidatedShowScore['show_id']] = $consolidatedShowScore;
            }
            unset($consolidatedShowScores);
            foreach ($shows as $key => $show) {
                $showInfo = [
                    'id' => $show->getId(),
                    'title' => u($show->getAllTitles())->truncate(240, '...', false),
                    'coverImage' => $show->getCoverImageLarge(),
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
                    'maxScore' => $maxScore,
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
