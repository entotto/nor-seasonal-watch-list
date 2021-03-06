<?php

namespace App\Controller;

use App\Entity\DiscordChannel;
use App\Form\DiscordChannelType;
use App\Repository\DiscordChannelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/discord/channel")
 */
class AdminDiscordChannelController extends AbstractController
{
    /**
     * @Route("/", name="admin_discord_channel_index", methods={"GET"})
     * @param DiscordChannelRepository $discordChannelRepository
     * @return Response
     */
    public function index(DiscordChannelRepository $discordChannelRepository): Response
    {
        return $this->render('discord_channel/index.html.twig', [
            'discord_channels' => $discordChannelRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_discord_channel_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $discordChannel = new DiscordChannel();
        $form = $this->createForm(DiscordChannelType::class, $discordChannel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($discordChannel);
            $show = $discordChannel->getShow();
            if ($show !== null) {
                $show->setDiscordChannel($discordChannel);
                $entityManager->persist($show);
            }
            $entityManager->flush();

            return $this->redirectToRoute('admin_discord_channel_index');
        }

        return $this->render('discord_channel/new.html.twig', [
            'discord_channel' => $discordChannel,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_discord_channel_show", methods={"GET"})
     * @param DiscordChannel $discordChannel
     * @return Response
     */
    public function show(DiscordChannel $discordChannel): Response
    {
        return $this->render('discord_channel/show.html.twig', [
            'discord_channel' => $discordChannel,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_discord_channel_edit", methods={"GET","POST"})
     * @param Request $request
     * @param DiscordChannel $discordChannel
     * @return Response
     */
    public function edit(Request $request, DiscordChannel $discordChannel): Response
    {
        $previousShow = $discordChannel->getShow();
        $form = $this->createForm(DiscordChannelType::class, $discordChannel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $show = $discordChannel->getShow();
            if ($previousShow !== null && ($show === null || $show->getId() !== $previousShow->getId())) {
                $previousShow->setDiscordChannel(null);
                $manager->persist($previousShow);
            }
            if ($show !== null) {
                $show->setDiscordChannel($discordChannel);
                $manager->persist($show);
            }
            $manager->flush();

            return $this->redirectToRoute('admin_discord_channel_index');
        }

        return $this->render('discord_channel/edit.html.twig', [
            'discord_channel' => $discordChannel,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_discord_channel_delete", methods={"DELETE"})
     * @param Request $request
     * @param DiscordChannel $discordChannel
     * @return Response
     */
    public function delete(Request $request, DiscordChannel $discordChannel): Response
    {
        if ($this->isCsrfTokenValid('delete'.$discordChannel->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $show = $discordChannel->getShow();
            if ($show !== null) {
                $show->setDiscordChannel(null);
                $entityManager->persist($show);
            }
            $entityManager->remove($discordChannel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_discord_channel_index');
    }
}
