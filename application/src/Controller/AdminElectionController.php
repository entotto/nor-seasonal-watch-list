<?php /** @noinspection UnknownInspectionInspection */

/** @noinspection PhpUnused */

namespace App\Controller;

use App\Entity\Election;
use App\Entity\ElectionShowBuff;
use App\Entity\Show;
use App\Entity\View\BuffedElection;
use App\Entity\View\VoteTally;
use App\Form\BuffedElectionType;
use App\Form\ElectionType;
use App\Repository\ElectionRepository;
use App\Repository\ElectionShowBuffRepository;
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
    public function index(
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        return $this->render('election/index.html.twig', [
            'user' => $this->getUser(),
            'elections' => $electionRepository->findBy([], ['startDate' => 'desc']),
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/new", name="admin_election_new", methods={"GET","POST"})
     * @param Request $request
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function new(
        Request $request,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
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
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_election_show", methods={"GET"}, requirements={"id":"\d+"})
     * @param Election $election
     * @param ElectionVoteRepository $electionVoteRepository
     * @param ShowRepository $showRepository
     * @param ElectionRepository $electionRepository
     * @return Response
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function show(
        Election $election,
        ElectionVoteRepository $electionVoteRepository,
        ShowRepository $showRepository,
        ElectionRepository $electionRepository
    ): Response {
        $info = $this->getVoterInfo($electionRepository, $showRepository, $election, $electionVoteRepository);
        return $this->render('election/show.html.twig', [
            'user' => $this->getUser(),
            'election' => $election,
            'votesInfo' => $info['votesInfo'],
            'totalVoterCount' => $info['buffedTotalVoterCount'],
            'voteTallies' => $info['voteTallies'],
            'electionIsActive' => $info['electionIsActive'],
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
        $totalVoterCount = $electionVoteRepository->getVoterCountForElection($election);
        $buffedTotalVoterCount = $electionVoteRepository->getBuffedVoterCountForElection($election);

        $filenameParts = [
            str_replace(' ', '-', $election->getSeason()->getName()),
            $election->getStartDate()->format('Ymd-Hi'),
            $election->getEndDate()->format('Ymd-Hi')
        ];
        $filename = implode('-', $filenameParts) . '.csv';

        $voteTallies = $this->getVoteTallies($votesInfo, $totalVoterCount, $buffedTotalVoterCount, $shows);

        $fp = fopen('php://temp', 'wb');
        fwrite($fp, $this->arrayToCsv(['Show', 'Raw Votes', 'Buff', 'Calc Votes', '% of Voters', '% of Total']) . "\n");
        foreach ($voteTallies as $voteTally) {
            $title = $voteTally->getShowCombinedTitle();
            if (!empty($voteTally->getRelatedShowNames())) {
                $title .= ' (and ' . count($voteTally->getRelatedShowNames()) . ' other seasons)';
            }
            fwrite($fp, $this->arrayToCsv([
                $title,
                $voteTally->getVoteCount(),
                "'" . $voteTally->getBuffRule(),
                $voteTally->getBuffedVoteCount(),
                $voteTally->getVotePercentOfVoterTotal(),
                $voteTally->getBuffedVotePercentOfTotal()
            ]) . "\n");
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
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function edit(
        Request $request,
        Election $election,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
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
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/{id}/buff", name="admin_election_buff", methods={"GET","POST"}, requirements={"id":"\d+"})
     * @param Request $request
     * @param Election $election
     * @param ElectionVoteRepository $electionVoteRepository
     * @param ShowRepository $showRepository
     * @param ElectionRepository $electionRepository
     * @param ElectionShowBuffRepository $electionShowBuffRepository
     * @return Response
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function buff(
        Request $request,
        Election $election,
        ElectionVoteRepository $electionVoteRepository,
        ShowRepository $showRepository,
        ElectionRepository $electionRepository,
        ElectionShowBuffRepository $electionShowBuffRepository
    ): Response {
        $info = $this->getVoterInfo($electionRepository, $showRepository, $election, $electionVoteRepository);
        $buffedElection = new BuffedElection($election);
        $buffedElection->setVoteTallies($info['voteTallies']);

        $form = $this->createForm(BuffedElectionType::class, $buffedElection);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($buffedElection->getVoteTallies() as $tally) {
                /** @var VoteTally $tally */
                $showId = $tally->getShowId();
                $buffRule = $tally->getBuffRule();
                $electionShowBuffs = $electionShowBuffRepository->findBy(['election' => $election->getId(), 'animeShow' => $showId]);
                if (empty($electionShowBuffs)) {
                    if (!empty($buffRule)) {
                        $electionShowBuff = new ElectionShowBuff();
                        $electionShowBuff->setElection($election);
                        $show = $showRepository->find($showId);
                        $electionShowBuff->setAnimeShow($show);
                        $electionShowBuff->setBuffRule($buffRule);
                        $em->persist($electionShowBuff);
                    }
                } else {
                    foreach ($electionShowBuffs as $key => $buff) {
                        if ($key === 0) {
                            if (empty($buffRule)) {
                                $em->remove($buff);
                            } else {
                                $buff->setBuffRule($buffRule);
                                $em->persist($buff);
                            }
                        } else {
                            $em->remove($buff);
                        }
                    }
                }
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_election_index');
        }

        return $this->render('election/buff.html.twig', [
            'user' => $this->getUser(),
            'election' => $election,
            'buffedElection' => $buffedElection,
            'form' => $form->createView(),
            'electionIsActive' => $info['electionIsActive'],
        ]);
    }

    /**
     * @Route("/{id}", name="admin_election_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     * @param Request $request
     * @param Election $election
     * @return Response
     */
    public function delete(
        Request $request,
        Election $election
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$election->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($election);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_election_index');
    }

    /**
     * @param array $votesInfo
     * @param int $totalVoterCount
     * @param int $buffedTotalVoterCount
     * @param Show[] $shows
     * @return VoteTally[]
     */
    private function getVoteTallies(array $votesInfo, int $totalVoterCount, int $buffedTotalVoterCount, array $shows): array
    {
        $voteTallies = [];
        $totalVotes = 0;
        $buffedTotalVotes = 0;
        foreach ($votesInfo as $voteInfo) {
            $totalVotes += $voteInfo['vote_count'];
            $buffedTotalVotes += $voteInfo['buffed_vote_count'];
        }
        foreach ($votesInfo as $key => $voteInfo) {
            $voteTally = new VoteTally();
            $voteTally->setId($key);
            $voteTally->setShowId((int)$voteInfo['show_id']);
            $voteTally->setShowJapaneseTitle((string)$voteInfo['japanese_title']);
            $voteTally->setShowFullJapaneseTitle((string)$voteInfo['full_japanese_title']);
            $voteTally->setShowEnglishTitle((string)$voteInfo['english_title']);
            $voteTally->setVoteCount((int)$voteInfo['vote_count']);
            $voteTally->setBuffedVoteCount((int)$voteInfo['buffed_vote_count']);
            $voteTally->setBuffRule($voteInfo['buff_rule'] ?? '');
            $voteTally->setVotePercentOfTotal($this->calculatePercent($voteInfo['vote_count'], $totalVotes));
            $voteTally->setBuffedVotePercentOfTotal($this->calculatePercent($voteInfo['buffed_vote_count'], $buffedTotalVotes));
            $voteTally->setVotePercentOfVoterTotal($this->calculatePercent($voteInfo['vote_count'], $totalVoterCount));
            $voteTally->setBuffedVotePercentOfVoterTotal($this->calculatePercent($voteInfo['buffed_vote_count'], $buffedTotalVoterCount));
            $voteTallies[] = $voteTally;
            foreach ($shows as $showsKey => $show) {
                if ($show->getId() === $voteTally->getShowId()) {
                    $voteTally->setShowCombinedTitle($show->getVoteStyleTitles());
                    if ($show->getRelatedShows()) {
                        $relatedShowNames = [];
                        foreach ($show->getRelatedShows() as $relatedShow) {
                            $relatedShowNames[] = $relatedShow->getVoteStyleTitles();
                        }
                        $voteTally->setRelatedShowNames($relatedShowNames);
                    }
                    unset($shows[$showsKey]);
                    break;
                }
            }
        }

        // Remaining $shows got zero votes
        $nextVoteTallyId = count($voteTallies);
        foreach ($shows as $show) {
            $nextVoteTallyId++;
            $voteTally = new VoteTally();
            $voteTally->setId($nextVoteTallyId);
            $voteTally->setShowId($show->getId());
            $voteTally->setShowCombinedTitle((string)$show->getVoteStyleTitles());
            $voteTally->setShowJapaneseTitle((string)$show->getJapaneseTitle());
            $voteTally->setShowFullJapaneseTitle((string)$show->getFullJapaneseTitle());
            $voteTally->setShowEnglishTitle((string)$show->getEnglishTitle());
            $voteTally->setVoteCount(0);
            $voteTally->setBuffedVoteCount(0);
            $voteTally->setVotePercentOfTotal(0.0);
            $voteTally->setBuffedVotePercentOfTotal(0.0);
            $voteTally->setRelatedShowNames([]);
            if ($show->getRelatedShows()) {
                foreach ($show->getRelatedShows() as $relatedShow) {
                    $voteTally->addRelatedShowName($relatedShow->getVoteStyleTitles());
                }
            }
            $voteTallies[] = $voteTally;
        }
        return $voteTallies;
    }

    private function calculatePercent(int $count, int $totalCount): float
    {
        if ($totalCount > 0) {
            return round(($count / $totalCount) * 100, 1);
        }
        return 0;
    }

    /**
     * @param ElectionRepository $electionRepository
     * @param ShowRepository $showRepository
     * @param Election $election
     * @param ElectionVoteRepository $electionVoteRepository
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function getVoterInfo(
        ElectionRepository $electionRepository,
        ShowRepository $showRepository,
        Election $election,
        ElectionVoteRepository $electionVoteRepository
    ): array {
        $info = [];
        $info['electionIsActive'] = $electionRepository->electionIsActive();
        $info['shows'] = $showRepository->getShowsForSeasonElectionEligible($election->getSeason());
        $info['votesInfo'] = $electionVoteRepository->getCountsForElection($election);
        $info['totalVoterCount'] = $electionVoteRepository->getVoterCountForElection($election);
        $info['buffedTotalVoterCount'] = $electionVoteRepository->getBuffedVoterCountForElection($election);
        $info['voteTallies'] = $this->getVoteTallies($info['votesInfo'], $info['totalVoterCount'],
            $info['buffedTotalVoterCount'], $info['shows']);
        return $info;
    }

    /**
     * Formats a line (passed as a fields  array) as CSV and returns the CSV as a string.
     * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120
     *
     * @param array $fields
     * @param string $delimiter
     * @param string $enclosure
     * @param bool $encloseAll
     * @param bool $nullToMysqlNull
     * @return string
     * @noinspection PhpSameParameterValueInspection
     */
    private function arrayToCsv(
        array $fields,
        string $delimiter = ',',
        string $enclosure = '"',
        bool $encloseAll = true,
        bool $nullToMysqlNull = false
    ): string {
        $delimiter_esc = preg_quote($delimiter, '/');
        $enclosure_esc = preg_quote($enclosure, '/');

        $output = array();
        foreach ( $fields as $field ) {
            if ($field === null && $nullToMysqlNull) {
                $output[] = 'NULL';
                continue;
            }

            // Enclose fields containing $delimiter, $enclosure or whitespace
            /** @noinspection RegExpUnnecessaryNonCapturingGroup */
            /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
            if ( $encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field ) ) {
                $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
            }
            else {
                $output[] = $field;
            }
        }

        return implode( $delimiter, $output );
    }
}
