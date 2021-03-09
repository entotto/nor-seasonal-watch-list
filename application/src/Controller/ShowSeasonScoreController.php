<?php /** @noinspection DuplicatedCode */

namespace App\Controller;

use App\Entity\ShowSeasonScore;
use App\Entity\User;
use App\Form\ShowSeasonScoreType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/show/season/score")
 */
class ShowSeasonScoreController extends AbstractController
{
    /**
     * @Route("/{id}/edit", name="admin_show_season_score_edit", methods={"GET","POST"})
     * @param Request $request
     * @param ShowSeasonScore $showSeasonScore
     * @return Response
     */
    public function edit(Request $request, ShowSeasonScore $showSeasonScore): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user === null) {
            return new JsonResponse(['data' => ['status' => 'permission_denied']], Response::HTTP_FORBIDDEN);
        }
        if ($showSeasonScore->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['data' => ['status' => 'permission_denied']], Response::HTTP_FORBIDDEN);
        }

        $form = $this->createForm(
            ShowSeasonScoreType::class,
            $showSeasonScore,
            [
                'attr' => [
                    'id' => 'show_season_score_' . $showSeasonScore->getId(),
                    'class' => 'show_season_score_form',
                ]
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            if ($request->isXmlHttpRequest()) {
                // Just send back fact of success
                return new JsonResponse(['data' => ['status' => 'success']]);
            }

            throw new UnauthorizedHttpException('This page should never be requested directly.');
        }

        if ($request->isXmlHttpRequest()) {
            // There was a validation error, return just the form
            $html = $this->renderView('show_season_score/_form.html.twig', [
                'form' => $form->createView(),
            ]);
            return new Response($html, 400);
        }

        throw new UnauthorizedHttpException('This page should never be requested directly.');
    }
}
