<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace App\Controller;

use App\Service\DiscordApi;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use http\Exception\RuntimeException;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wohali\OAuth2\Client\Provider\Discord;

class DiscordController extends AbstractController
{
    /**
     * @Route("/discord", name="discord_index")
     * @param Request $request
     * @param DiscordApi $discordApi
     * @param string $discordClientId
     * @param string $discordClientSecret
     * @param string $norGuildId
     * @param string $discordTestRedirectUri
     * @return Response
     * @throws GuzzleException
     * @throws IdentityProviderException
     * @throws Exception
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function index(
        Request $request,
        DiscordApi $discordApi,
        string $discordClientId,
        string $discordClientSecret,
        string $norGuildId,
        string $discordTestRedirectUri
    ): Response {
        $options = [
            'state' => bin2hex(random_bytes(20)),
            'scope' => [
                'identify',
                'guilds',
            ]
        ];
        $provider = new Discord([
            'clientId' => $discordClientId,
            'clientSecret' => $discordClientSecret,
            'redirectUri' => $discordTestRedirectUri,
            'options' => $options,
        ]);

        $session = $request->getSession();

        $code = $request->get('code');
        $state = $request->get('state');
        $oauth2state = $session->get('oauth2state');

        if (!$code) {
            $authUrl = $provider->getAuthorizationUrl($options);
            $session->set('oauth2state', $provider->getState());
            return $this->redirect($authUrl);
        }

        if (!$state || $state !== $oauth2state) {
            $session->remove('oauth2state');
            throw new RuntimeException('Invalid oauth state while authenticating with Discord');
        }

        $token = $provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);

        try {
            $user = $provider->getResourceOwner($token);
            $username = $user->getUsername();
            $discriminator = $user->getDiscriminator();
            $userArray = $user->toArray();
            $userId = $user->getId();
            $expiresDT = new DateTime('@' . $token->getExpires());

            $discordApi->initialize($token->getToken());
            $meInfo = $discordApi->getMe();

            $myGuildsInfo = $discordApi->getMyGuilds();

            $myUHCGuildInfo = $discordApi->getGuildMemberInfo($norGuildId, $userId);
            $guildInfo = $discordApi->getGuild($norGuildId);
            $guildRoles = $discordApi->getGuildRoles($norGuildId);
            $guildRolesForMember = $discordApi->getGuildRolesForMember($norGuildId, $userId);
        } catch (Exception $e) {
            $username = 'unknown (error: ' . $e->getMessage() . ')';
            $discriminator = '';
            $userArray = [];
            $expiresDT = null;
            $meInfo = [];
            $myGuildsInfo = [];
            $myUHCGuildInfo = [];
            $guildInfo = null;
            $guildRoles = null;
            $guildRolesForMember = null;
        }
        return $this->render('discord/index.html.twig', [
            'controller_name' => 'DiscordController',
            'token' => $token->getToken(),
            'refreshToken' => $token->getRefreshToken(),
            'expires' => $token->getExpires(),
            'expiresDt' => $expiresDT,
            'tokenHasExpired' => ($token->hasExpired() ? 'yes' : 'no'),
            'username' => $username,
            'discriminator' => $discriminator,
            'userArray' => $userArray,
            'meInfo' => $meInfo,
            'myGuildsInfo' => $myGuildsInfo,
            'myUHCGuildInfo' => $myUHCGuildInfo,
            'guildInfo' => $guildInfo,
            'guildRoles' => $guildRoles,
            'guildRolesForMember' => $guildRolesForMember,
        ]);
    }

    /**
     * Link to this controller to start the "connect" process
     *
     * @param ClientRegistry $clientRegistry
     * @param string $discordRedirectUri
     * @return RedirectResponse
     * @Route("/discord/connect", name="connect_discord_start")
     */
    public function connectAction(ClientRegistry $clientRegistry, string $discordRedirectUri): RedirectResponse
    {
        return $clientRegistry
            ->getClient('discord')
            ->redirect(
                ['identify', 'guilds'],
                ['redirect_uri' => $discordRedirectUri]
            );
    }

    /**
     * After going to Discord, the user will be redirected back here because this is the
     * 'redirect_route' configured in config/packages/knpu_oauth2_client.yaml
     *
     * @param Request $request
     * @param ClientRegistry $clientRegistry
     *
     * @return void
     * @Route("/discord_check", name="secure_connect_discord_check", schemes={"https"})
     */
    public function secureConnectCheckAction(
        Request $request,
        ClientRegistry $clientRegistry
    ): void {
        $this->connectCheckAction($request, $clientRegistry);
    }

    /**
     * After going to Discord, the user will be redirected back here because this is the
     * 'redirect_route' configured in config/packages/knpu_oauth2_client.yaml
     *
     * @param Request $request
     * @param ClientRegistry $clientRegistry
     *
     * @Route("/discord_check", name="connect_discord_check", schemes={"http"})
     */
    public function connectCheckAction(
        Request $request,
        ClientRegistry $clientRegistry
    ): void {
        // This method left blank, authentication handled in the Guard authenticator
    }

    /**
     * Logout from the discord session
     *
     * @Route("/discord/disconnect", name="connect_discord_disconnect", methods={"GET"})
     */
    public function disconnectAction(): void
    {
        // This method left blank, handled in the Guard authenticator
    }

}
