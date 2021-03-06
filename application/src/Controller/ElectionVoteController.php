<?php

namespace App\Controller;

use App\Entity\ElectionVote;
use App\Form\ElectionVoteType;
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
     * @return Response
     */
    public function edit(Request $request, ElectionVote $electionVote): Response
    {
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
            $this->getDoctrine()->getManager()->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['data' => ['status' => 'success']]);
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

        throw new UnauthorizedHttpException('This page should never be requested directly.');
    }
}
