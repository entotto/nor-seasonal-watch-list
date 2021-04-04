<?php

namespace App\Controller;

use App\Entity\ShowSeasonScore;
use App\Form\ShowSeasonScoreType;
use App\Repository\ShowSeasonScoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/show/season/score")
 */
class AdminShowSeasonScoreController extends AbstractController
{
    /**
     * @Route("/", name="admin_show_season_score_index", options={"expose"=true}, methods={"GET"})
     * @param ShowSeasonScoreRepository $showSeasonScoreRepository
     * @return Response
     */
    public function index(ShowSeasonScoreRepository $showSeasonScoreRepository): Response
    {
        return $this->render('show_season_score/index.html.twig', [
            'user' => $this->getUser(),
            'show_season_scores' => $showSeasonScoreRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_show_season_score_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $showSeasonScore = new ShowSeasonScore();
        $form = $this->createForm(ShowSeasonScoreType::class, $showSeasonScore);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($showSeasonScore);
            $entityManager->flush();

            return $this->redirectToRoute('admin_show_season_score_index');
        }

        return $this->render('show_season_score/new.html.twig', [
            'user' => $this->getUser(),
            'show_season_score' => $showSeasonScore,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_show_season_score_show", methods={"GET"})
     * @param ShowSeasonScore $showSeasonScore
     * @return Response
     */
    public function show(ShowSeasonScore $showSeasonScore): Response
    {
        return $this->render('show_season_score/show.html.twig', [
            'user' => $this->getUser(),
            'show_season_score' => $showSeasonScore,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_show_season_score_edit", methods={"GET","POST"})
     * @param Request $request
     * @param ShowSeasonScore $showSeasonScore
     * @return Response
     */
    public function edit(Request $request, ShowSeasonScore $showSeasonScore): Response
    {
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

            return $this->redirectToRoute('admin_show_season_score_index');
        }

        if ($request->isXmlHttpRequest()) {
            // There was a validation error, return just the form
            $html = $this->renderView('show_season_score/_form.html.twig', [
                'form' => $form->createView(),
            ]);
            return new Response($html, 400);
        }

        return $this->render('show_season_score/edit.html.twig', [
            'user' => $this->getUser(),
            'show_season_score' => $showSeasonScore,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_show_season_score_delete", methods={"DELETE"})
     * @param Request $request
     * @param ShowSeasonScore $showSeasonScore
     * @return Response
     */
    public function delete(Request $request, ShowSeasonScore $showSeasonScore): Response
    {
        if ($this->isCsrfTokenValid('delete'.$showSeasonScore->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($showSeasonScore);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_show_season_score_index');
    }
}
