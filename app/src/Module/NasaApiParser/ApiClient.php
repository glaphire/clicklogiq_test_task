<?php

namespace App\Module\NasaApiParser;

use App\Module\NasaApiParser\Exception\NasaApiException;
use DateTimeImmutable;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class ApiClient
{
    private Client $httpClient;

    private string $nasaApiKey;

    const URL_NEO_LIST = 'neo/rest/v1/feed';

    public function __construct(Client $httpClient, string $nasaApiKey)
    {
        $this->httpClient = $httpClient;
        $this->nasaApiKey = $nasaApiKey;
    }

    public function listNearEarthObjects(DateTimeImmutable $startDate, ?DateTimeImmutable $endDate = null): ?string
    {
        $endDate = $endDate ?? new DateTimeImmutable();

        $response = $this->httpClient->get(self::URL_NEO_LIST,
            [
                'query' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'detailed' => 'false',
                    'api_key' => $this->nasaApiKey
                ],
            ]);

        //TODO: refactor to avoid returning null when exception is thrown obviously
        if ($this->isResponseStatusSuccessful($response)) {
            return $response->getBody()->getContents();
        }

        return null;
    }

    private function isResponseStatusSuccessful($response): bool
    {
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new NasaApiException(
                sprintf(
                    'Failed to get response from %s: %s HTTP status code with message "%s"',
                    self::URL_NEO_LIST,
                    $response->getStatusCode(),
                    $response->getBody()
                )
            );
        }

        return true;
    }
}
