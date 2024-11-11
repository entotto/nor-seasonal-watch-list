<?php /** @noinspection PhpUndefinedClassInspection */

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class SwlApi
{
    private string $rootUrl;
    private string $apiKey;

    /**
     * SwlApi constructor.
     * @param string $rootUrl
     * @param string $botToken
     */
    public function __construct(
        string $rootUrl,
        string $apiKey
    ) {
        $this->rootUrl = $rootUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * @return array
     * @throws GuzzleException|JsonException
     */
    public function getSeasons(): array
    {
        $result = $this->sendRequest('GET', '/seasons');
        return json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param int $seasonId
     * @return array
     * @throws GuzzleException
     * @throws JsonException
     */
    public function getSeasonsCommunityWatchesById(int $seasonId): array
    {
        $result = $this->sendRequest('GET', '/seasons/' . $seasonId . '/community-watches');
        return json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param $method
     * @param $endpoint
     * @param null $body
     * @return ResponseInterface|null
     * @throws GuzzleException
     */
    public function sendRequest($method, $endpoint, $body = null): ?ResponseInterface
    {
        if ($this->apiKey === null) {
            throw new RuntimeException('API key not set, library has not been initialized');
        }
        $client = new Client(['exceptions' => false]);
        return $client->request($method, $this->rootUrl . $endpoint, [
            'body' => $body,
            'headers' => [ 'X-Auth-Token' => $this->apiKey],
            'http_errors' => false
        ]);
    }
}
