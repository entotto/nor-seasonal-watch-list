<?php

namespace App\Controller;

use App\Entity\ElectionVote;
use App\Form\ElectionVoteType;
use App\Repository\ElectionRepository;
use App\Repository\ElectionVoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/election/vote")
 */
class AdminElectionVoteController extends AbstractController
{
    /**
     * @Route("/", name="admin_election_vote_index", methods={"GET"})
     * @param ElectionVoteRepository $electionVoteRepository
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function index(
        ElectionVoteRepository $electionVoteRepository,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        return $this->render('election_vote/index.html.twig', [
            'user' => $this->getUser(),
            'election_votes' => $electionVoteRepository->findAll(),
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/new", name="admin_election_vote_new", methods={"GET","POST"})
     * @param Request $request
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function new(
        Request $request,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        $electionVote = new ElectionVote();
        $form = $this->createForm(ElectionVoteType::class, $electionVote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($electionVote);
            $entityManager->flush();

            return $this->redirectToRoute('admin_election_vote_index');
        }

        return $this->render('election_vote/new.html.twig', [
            'user' => $this->getUser(),
            'election_vote' => $electionVote,
            'form' => $form->createView(),
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_election_vote_show", methods={"GET"}, requirements={"id":"\d+"})
     * @param ElectionVote $electionVote
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function show(
        ElectionVote $electionVote,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        return $this->render('election_vote/show.html.twig', [
            'user' => $this->getUser(),
            'election_vote' => $electionVote,
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_election_vote_edit", methods={"GET","POST"}, requirements={"id":"\d+"})
     * @param Request $request
     * @param ElectionVote $electionVote
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function edit(
        Request $request,
        ElectionVote $electionVote,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        $form = $this->createForm(ElectionVoteType::class, $electionVote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_election_vote_index');
        }

        return $this->render('election_vote/edit.html.twig', [
            'user' => $this->getUser(),
            'election_vote' => $electionVote,
            'form' => $form->createView(),
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_election_vote_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     * @param Request $request
     * @param ElectionVote $electionVote
     * @return Response
     */
    public function delete(Request $request, ElectionVote $electionVote): Response
    {
        if ($this->isCsrfTokenValid('delete'.$electionVote->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($electionVote);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_election_vote_index');
    }
}
