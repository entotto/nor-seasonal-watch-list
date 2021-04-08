<?php

namespace App\Controller;

use App\Entity\Election;
use App\Entity\View\VoteTally;
use App\Form\ElectionType;
use App\Repository\ElectionRepository;
use App\Repository\ElectionVoteRepository;
use App\Repository\ShowRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/election")
 */
class AdminElectionController extends AbstractController
{
    /**
     * @Route("/", name="admin_election_index", methods={"GET"})
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function index(ElectionRepository $electionRepository): Response
    {
        return $this->render('election/index.html.twig', [
            'user' => $this->getUser(),
            'elections' => $electionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_election_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $election = new Election();
        $form = $this->createForm(ElectionType::class, $election);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($election);
            $entityManager->flush();

            return $this->redirectToRoute('admin_election_index');
        }

        return $this->render('election/new.html.twig', [
            'user' => $this->getUser(),
            'election' => $election,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_election_show", methods={"GET"}, requirements={"id":"\d+"})
     * @param Election $election
     * @param ElectionVoteRepository $electionVoteRepository
     * @param ShowRepository $showRepository
     * @return Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function show(
        Election $election,
        ElectionVoteRepository $electionVoteRepository,
        ShowRepository $showRepository
    ): Response {
        $shows = $showRepository->getShowsForSeasonElectionEligible($election->getSeason());
        $votesInfo = $electionVoteRepository->getCountsForElection($election);

        $voteTallies = $this->getVoteTallies($votesInfo, $shows);

        return $this->render('election/show.html.twig', [
            'user' => $this->getUser(),
            'election' => $election,
            'votesInfo' => $votesInfo,
            'voteTallies' => $voteTallies
        ]);
    }

    /**
     * Export election data as a CSV file
     *
     * @Route("/export/{id}", name="admin_election_export", methods={"GET"}, requirements={"id":"\d+"})
     * @param Election $election
     * @param ElectionVoteRepository $electionVoteRepository
     * @param ShowRepository $showRepository
     * @return Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function export(
        Election $election,
        ElectionVoteRepository $electionVoteRepository,
        ShowRepository $showRepository
    ): Response {
        $shows = $showRepository->getShowsForSeasonElectionEligible($election->getSeason());
        $votesInfo = $electionVoteRepository->getCountsForElection($election);

        $filenameParts = [
            str_replace(' ', '-', $election->getSeason()->getName()),
            $election->getStartDate()->format('Ymd-Hi'),
            $election->getEndDate()->format('Ymd-Hi')
        ];
        $filename = implode('-', $filenameParts) . '.csv';

        $voteTallies = $this->getVoteTallies($votesInfo, $shows);

        $fp = fopen('php://temp', 'wb');
        fputcsv($fp, ['Show', 'Votes', '% of Total']);
        foreach ($voteTallies as $voteTally) {
            fputcsv($fp, [
                $voteTally->getShowJapaneseTitle() . ' (' .
                $voteTally->getShowFullJapaneseTitle() . ') ' .
                $voteTally->getShowEnglishTitle(),
                $voteTally->getVoteCount(),
                $voteTally->getVotePercentOfTotal()
            ]);
        }

        rewind($fp);
        $response = new Response(stream_get_contents($fp));
        fclose($fp);

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        return $response;
    }

    /**
     * @Route("/{id}/edit", name="admin_election_edit", methods={"GET","POST"}, requirements={"id":"\d+"})
     * @param Request $request
     * @param Election $election
     * @return Response
     */
    public function edit(Request $request, Election $election): Response
    {
        $form = $this->createForm(ElectionType::class, $election);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_election_index');
        }

        return $this->render('election/edit.html.twig', [
            'user' => $this->getUser(),
            'election' => $election,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_election_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     * @param Request $request
     * @param Election $election
     * @return Response
     */
    public function delete(Request $request, Election $election): Response
    {
        if ($this->isCsrfTokenValid('delete'.$election->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($election);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_election_index');
    }

    /**
     * @param array $votesInfo
     * @param array $shows
     * @return VoteTally[]
     */
    private function getVoteTallies(array $votesInfo, array $shows): array
    {
        $voteTallies = [];
        $totalVotes = 0;
        foreach ($votesInfo as $voteInfo) {
            $totalVotes += $voteInfo['vote_count'];
        }
        foreach ($votesInfo as $key => $voteInfo) {
            $voteTally = new VoteTally();
            $voteTally->setId($key);
            $voteTally->setShowId((int)$voteInfo['show_id']);
            $voteTally->setShowJapaneseTitle((string)$voteInfo['japanese_title']);
            $voteTally->setShowFullJapaneseTitle((string)$voteInfo['full_japanese_title']);
            $voteTally->setShowEnglishTitle((string)$voteInfo['english_title']);
            $voteTally->setVoteCount((int)$voteInfo['vote_count']);
            $voteTally->setVotePercentOfTotal($this->calculatePercent($voteInfo['vote_count'], $totalVotes));
            $voteTallies[] = $voteTally;
            foreach ($shows as $showsKey => $show) {
                if ($show->getId() === $voteTally->getShowId()) {
                    unset($shows[$showsKey]);
                    break;
                }
            }
        }

        // Remaining $shows got zero votes
        $nextVoteTallyId = count($voteTallies);
        foreach ($shows as $key => $show) {
            $nextVoteTallyId++;
            $voteTally = new VoteTally();
            $voteTally->setId($nextVoteTallyId);
            $voteTally->setShowId($show->getId());
            $voteTally->setShowJapaneseTitle((string)$show->getJapaneseTitle());
            $voteTally->setShowFullJapaneseTitle((string)$show->getFullJapaneseTitle());
            $voteTally->setShowEnglishTitle((string)$show->getEnglishTitle());
            $voteTally->setVoteCount(0);
            $voteTally->setVotePercentOfTotal(0.0);
            $voteTallies[] = $voteTally;
        }
        return $voteTallies;
    }

    private function calculatePercent(int $count, int $totalCount): float
    {
        return round(($count / $totalCount) * 100, 1);
    }
}
