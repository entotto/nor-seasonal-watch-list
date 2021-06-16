<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Form\ActivityType;
use App\Repository\ActivityRepository;
use App\Repository\ElectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/activity")
 */
class AdminActivityController extends AbstractController
{
    /**
     * @Route("/", name="admin_activity_index", methods={"GET"})
     * @param ActivityRepository $activityRepository
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function index(
        ActivityRepository $activityRepository,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        return $this->render('activity/index.html.twig', [
            'user' => $this->getUser(),
            'activities' => $activityRepository->findAll(),
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/new", name="admin_activity_new", methods={"GET","POST"})
     * @param Request $request
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function new(
        Request $request,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        $activity = new Activity();
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setIcon($activity->getIcon());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($activity);
            $entityManager->flush();

            return $this->redirectToRoute('admin_activity_index');
        }

        return $this->render('activity/new.html.twig', [
            'user' => $this->getUser(),
            'activity' => $activity,
            'form' => $form->createView(),
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_activity_show", methods={"GET"})
     * @param Activity $activity
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function show(
        Activity $activity,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        return $this->render('activity/show.html.twig', [
            'user' => $this->getUser(),
            'activity' => $activity,
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_activity_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Activity $activity
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function edit(
        Request $request,
        Activity $activity,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setIcon($activity->getIcon());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_activity_index');
        }

        return $this->render('activity/edit.html.twig', [
            'user' => $this->getUser(),
            'activity' => $activity,
            'form' => $form->createView(),
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_activity_delete", methods={"DELETE"})
     * @param Request $request
     * @param Activity $activity
     * @return Response
     */
    public function delete(Request $request, Activity $activity): Response
    {
        if ($this->isCsrfTokenValid('delete'.$activity->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($activity);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_activity_index');
    }
}
