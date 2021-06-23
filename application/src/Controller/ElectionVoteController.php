<?php

namespace App\Controller;

use App\Entity\ElectionVote;
use App\Entity\User;
use App\Form\ElectionVoteType;
use App\Repository\ElectionVoteRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/election/vote")
 */
class ElectionVoteController extends AbstractController
{
    /**
     * @Route("/{id}/edit", name="election_vote_edit", methods={"GET","POST"}, requirements={"id":"\d+"})
     * @param Request $request
     * @param ElectionVote $electionVote
     * @param ElectionVoteRepository $electionVoteRepository
     * @return Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function edit(
        Request $request,
        ElectionVote $electionVote,
        ElectionVoteRepository $electionVoteRepository
    ): Response
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            if ($user === null) {
                return new JsonResponse(['data' => ['status' => 'permission_denied']], Response::HTTP_FORBIDDEN);
            }
            if ($electionVote->getUser()->getId() !== $user->getId()) {
                return new JsonResponse(['data' => ['status' => 'permission_denied']], Response::HTTP_FORBIDDEN);
            }

            $election = $electionVote->getElection();
            $maxVotes = $election->getMaxVotes();
            $currentVoteCount = $electionVoteRepository->getCountForUserAndElection($user, $election);

            $form = $this->createForm(
                ElectionVoteType::class,
                $electionVote,
                [
                    'attr' => [
                        'id' => 'election_vote_' . $electionVote->getId(),
                        'class' => 'election_vote_form',
                    ]
                ]
            );
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($currentVoteCount >= $maxVotes && $electionVote->getChosen()) {
                    $electionVote->setChosen(false);
                    $this->getDoctrine()->getManager()->persist($electionVote);
                    $this->getDoctrine()->getManager()->flush();
                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse(
                            ['data' => [
                                'status' => 'failure',
                                'message' => 'Too many votes, limit is ' . $maxVotes . '. Please unselect another choice first.'
                            ]],
                            Response::HTTP_BAD_REQUEST
                        );
                    }

                    throw new UnauthorizedHttpException('This page should never be requested directly.');
                }
                $this->getDoctrine()->getManager()->flush();

                if ($request->isXmlHttpRequest()) {
                    $newCurrentVoteCount = ($electionVote->getChosen()) ? $currentVoteCount + 1 : $currentVoteCount - 1;
                    $remainingChoices = $maxVotes - $newCurrentVoteCount;
                    switch ($remainingChoices) {
                        case 0:
                            $message = $maxVotes === 1 ? 'Vote received.' : 'All votes received.';
                            break;
                        case 1:
                            if ($newCurrentVoteCount > $currentVoteCount) {
                                $message = 'Vote received, 1 choice left.';
                            } else {
                                $message = 'Change received, 1 choice left.';
                            }
                            break;
                        default:
                            if ($newCurrentVoteCount > $currentVoteCount) {
                                $message = 'Vote received, ' . $remainingChoices . ' choices left.';
                            } else {
                                $message = 'Change received, ' . $remainingChoices . ' choices left.';
                            }

                    }
                    return new JsonResponse(
                        ['data' => [
                            'status' => 'success',
                            'message' => $message
                        ]]
                    );
                }

                throw new UnauthorizedHttpException('This page should never be requested directly.');
            }

            if ($request->isXmlHttpRequest()) {
                $html = $this->renderView('election_vote/edit.html.twig', [
                    'election_vote' => $electionVote,
                    'form' => $form->createView(),
                ]);
                return new Response($html, 400);
            }
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(
                ['data' => [
                    'status' => 'failed',
                    'message' => 'Internal error, please reload the page.'
                ]],
                Response::HTTP_BAD_REQUEST);
        }

        throw new UnauthorizedHttpException('This page should never be requested directly.');
    }
}
