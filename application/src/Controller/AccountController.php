<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPreferencesType;
use App\Repository\ElectionRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/account")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/preferences", name="account_preferences", methods={"GET","POST"})
     * @param Request $request
     * @param ElectionRepository $electionRepository
     * @return Response
     */
    public function index(
        Request $request,
        ElectionRepository $electionRepository
    ): Response {
        $electionIsActive = $electionRepository->electionIsActive();
        /** @var User $user */
        $user = $this->getUser();
        $preferences = $user->getPreferences();
        $form = $this->createForm(UserPreferencesType::class, $preferences);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $colorsMode = $form->get('colors_mode_picker')->getData();
            $swlViewMode = $form->get('all_watches_view_mode_picker')->getData();
            $prefs = $user->getPreferences();
            $prefs->setColorsMode($colorsMode);
            $prefs->setAllWatchesViewMode($swlViewMode);
            $user->setPreferences($prefs);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        return $this->render('account/preferences.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'electionIsActive' => $electionIsActive,
        ]);
    }

    /**
     * @Route("/preferences/reset_api_key", name="account_preferences_reset_api_key", methods={"POST"})
     * @return Response
     */
    public function resetApiKey(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        try {
            $user->setApiKey(sha1(random_bytes(20)));
        } catch (Exception $e) {
            $user->setApiKey(null);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('account_preferences');
    }

    public function clearApiKey(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setApiKey(null);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('account_preferences');
    }
}
