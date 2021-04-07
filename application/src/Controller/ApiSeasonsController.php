<?php /** @noinspection UnknownInspectionInspection */

/** @noinspection PhpUnused */

namespace App\Controller;

use App\Repository\SeasonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiSeasonsController extends AbstractController
{
    /**
     * @Route("/api/v1/seasons", name="api_seasons", methods={"GET"})
     * @param SeasonRepository $seasonRepository
     * @return JsonResponse
     */
    public function index(SeasonRepository $seasonRepository): JsonResponse
    {
        $seasons = $seasonRepository->getAllInRankOrder();
        $data = [];
        foreach ($seasons as $season) {
            $data[] = $season->jsonSerialize();
        }
        return new JsonResponse($data);
    }
}
