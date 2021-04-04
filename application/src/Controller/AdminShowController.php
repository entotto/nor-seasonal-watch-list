<?php

namespace App\Controller;

use App\Entity\Show;
use App\Form\ShowType;
use App\Repository\ShowRepository;
use App\Service\AnilistApi;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/show")
 */
class AdminShowController extends AbstractController
{
    /**
     * @Route("/", name="admin_show_index", methods={"GET"})
     * @param ShowRepository $showRepository
     * @return Response
     */
    public function index(ShowRepository $showRepository): Response
    {
        return $this->render('show/index.html.twig', [
            'user' => $this->getUser(),
            'shows' => $showRepository->getShowsSorted('romaji'),
        ]);
    }

    /**
     * @Route("/new", name="admin_show_new", methods={"GET","POST"})
     * @param Request $request
     * @param AnilistApi $anilistApi
     * @return Response
     * @throws GuzzleException
     */
    public function new(Request $request, AnilistApi $anilistApi): Response
    {
        $show = new Show();
        $form = $this->createForm(ShowType::class, $show);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $anilistData = $anilistApi->fetch($show->getAnilistId());
            if ($anilistData !== null) {
                $anilistApi->updateShow($show, $anilistData);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($show);
            $entityManager->flush();

            return $this->redirectToRoute('admin_show_index');
        }

        return $this->render('show/new.html.twig', [
            'user' => $this->getUser(),
            'show' => $show,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_show_show", methods={"GET"})
     * @param Show $show
     * @return Response
     */
    public function show(Show $show): Response
    {
        return $this->render('show/show.html.twig', [
            'user' => $this->getUser(),
            'show' => $show,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_show_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Show $show
     * @param AnilistApi $anilistApi
     * @return Response
     * @throws GuzzleException
     */
    public function edit(Request $request, Show $show, AnilistApi $anilistApi): Response
    {
        $form = $this->createForm(ShowType::class, $show);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $anilistData = $anilistApi->fetch($show->getAnilistId());
            if ($anilistData !== null) {
                $anilistApi->updateShow($show, $anilistData);
            }
            $this->getDoctrine()->getManager()->persist($show);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_show_index');
        }

        return $this->render('show/edit.html.twig', [
            'user' => $this->getUser(),
            'show' => $show,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_show_delete", methods={"DELETE"})
     * @param Request $request
     * @param Show $show
     * @return Response
     */
    public function delete(Request $request, Show $show): Response
    {
        if ($this->isCsrfTokenValid('delete'.$show->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($show);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_show_index');
    }
}
