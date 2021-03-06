<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

namespace App\Controller;

use App\Entity\ShowSeasonScore;
use App\Entity\User;
use App\Form\ShowSeasonScoreType;
use App\Repository\SeasonRepository;
use App\Repository\ShowRepository;
use App\Repository\ShowSeasonScoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyWatchController extends AbstractController
{
    /**
     * @Route("/my/watch", name="my_watch_index", options={"expose"=true})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param SeasonRepository $seasonRepository
     * @param ShowRepository $showRepository
     * @param ShowSeasonScoreRepository $showSeasonScoreRepository
     * @return Response
     * @throws NonUniqueResultException
     */
    public function index(
        Request $request,
        EntityManagerInterface $em,
        SeasonRepository $seasonRepository,
        ShowRepository $showRepository,
        ShowSeasonScoreRepository $showSeasonScoreRepository
    ): Response {
        $seasons = $seasonRepository->getAllInRankOrder();
        $selectedSeasonId = $request->get('season');

        /** @var User $user */
        $user = $this->getUser();
        $data = [];
        if ($selectedSeasonId === null) {
            $season = $seasonRepository->getSeasonForDate();
            if ($season === null) {
                $season = $seasonRepository->getFirstSeason();
            }
        } else {
            $season = $seasonRepository->find($selectedSeasonId);
        }
        if ($season !== null) {
            $selectedSeasonId = $season->getId();
            $shows = $showRepository->getShowsForSeason($season);
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
                    $em->persist($score);
                    $em->flush();
                }
                $form = $this->createForm(
                    ShowSeasonScoreType::class,
                    $score,
                    [
                        'attr' => [
                            'id' => 'list_my_watch_form_' . $key,
                            'class' => 'list_my_watch_form',
                        ],
                        'show_score_only' => true,
                        'form_key' => $key,
                        'action' => $this->generateUrl('admin_show_season_score_edit', ['id' => $score->getId()])
                    ]
                );
                $data[] = ['score' => $score, 'form' => $form->createView()];
            }
        }
        $em->flush();

        return $this->render('my_watch/index.html.twig', [
            'controller_name' => 'MyWatchController',
            'seasons' => $seasons,
            'selectedSeasonId' => $selectedSeasonId,
            'data' => $data,
        ]);
    }
}
