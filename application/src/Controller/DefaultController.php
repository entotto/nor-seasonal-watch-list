<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default", schemes={"HTTP"})
     */
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'user' => $user,
        ]);
    }

    /**
     * @Route("/", name="https_default", schemes={"HTTPS"})
     */
    public function indexHttps(): Response
    {
        return $this->index();
    }

}
