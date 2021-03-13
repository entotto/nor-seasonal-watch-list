<?php

namespace App\Service;

use App\Entity\Season;
use App\Repository\SeasonRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

final class SelectedSeasonHelper
{
    /**
     * @var SeasonRepository
     */
    private SeasonRepository $seasonRepository;

    /**
     * SelectedSeasonHelper constructor.
     * @param SeasonRepository $seasonRepository
     */
    public function __construct(SeasonRepository $seasonRepository)
    {
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * @param Request $request
     * @return Season|null
     * @throws NonUniqueResultException
     */
    public function getSelectedSeason(Request $request): ?Season
    {
        $selectedSeasonId = $request->get('season');
        if ($selectedSeasonId === null) {
            $selectedSeasonId = $request->getSession()->get('selectedSeasonId', null);
        }
        if ($selectedSeasonId === null) {
            $season = $this->seasonRepository->getSeasonForDate();
            if ($season === null) {
                $season = $this->seasonRepository->getFirstSeason();
            }
        } else {
            $season = $this->seasonRepository->find($selectedSeasonId);
        }
        $request->getSession()->set('selectedSeasonId', $selectedSeasonId);
        return $season;
    }

}
