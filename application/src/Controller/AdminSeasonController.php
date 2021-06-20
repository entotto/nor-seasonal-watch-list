<?php

namespace App\Controller;

use App\Entity\Season;
use App\Form\SeasonType;
use App\Repository\ElectionRepository;
use App\Repository\SeasonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/season")
 */
class AdminSeasonController extends AbstractController
{
    /**
     * @Route("/", name="admin_season_index", methods={"GET"})
     * @param SeasonRepository $seasonRepository
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function index(
        SeasonRepository $seasonRepository,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        return $this->render('season/index.html.twig', [
            'user' => $this->getUser(),
            'seasons' => $seasonRepository->getAllInRankOrder(true),
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/new", name="admin_season_new", methods={"GET","POST"})
     * @param Request $request
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function new(
        Request $request,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        $season = new Season();
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($season);
            $entityManager->flush();

            return $this->redirectToRoute('admin_season_index');
        }

        return $this->render('season/new.html.twig', [
            'user' => $this->getUser(),
            'season' => $season,
            'form' => $form->createView(),
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_season_show", methods={"GET"})
     * @param Season $season
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function show(
        Season $season,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        return $this->render('season/show.html.twig', [
            'user' => $this->getUser(),
            'season' => $season,
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_season_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Season $season
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function edit(
        Request $request,
        Season $season,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_season_index');
        }

        return $this->render('season/edit.html.twig', [
            'user' => $this->getUser(),
            'season' => $season,
            'form' => $form->createView(),
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_season_delete", methods={"DELETE"})
     * @param Request $request
     * @param Season $season
     * @return Response
     */
    public function delete(Request $request, Season $season): Response
    {
        if ($this->isCsrfTokenValid('delete'.$season->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($season);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_season_index');
    }
}
