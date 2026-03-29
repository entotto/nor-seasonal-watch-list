<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class DiscordAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private RequestStack $requestStack;

    public function __construct(UrlGeneratorInterface $urlGenerator, RequestStack $requestStack)
    {
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
    {
        // Store the original request URI in the session so we can redirect back after login
        $session = $this->requestStack->getSession();
        $session->set('loginOriginalRequestUri', $request->getUri());

        // Redirect to the Discord OAuth start route
        return new RedirectResponse($this->urlGenerator->generate('connect_discord_start'));
    }
}