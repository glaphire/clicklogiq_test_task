<?php

namespace App\Module\NasaApiParser;

use App\Module\NasaApiParser\Exception\NasaApiParserException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class ParserService
{
    private Client $httpClient;

    private string $nasaApiKey;

    private EntityManagerInterface $entityManager;

    private const NASA_API_BASE_URL = 'https://api.nasa.gov';

    public function __construct(Client $httpClient, EntityManagerInterface $entityManager, string $nasaApiKey)
    {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->nasaApiKey = $nasaApiKey;
    }

    public function getNearEarthObjectsForThreeLastDays()
    {
        $endDate = new DateTimeImmutable();
        //TODO: refactor to any days amount
        $startDate = $endDate->modify('-3 days');


        $response = $this->httpClient->get(self::NASA_API_BASE_URL . '/neo/rest/v1/feed',
            [
                'query' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'detailed' => 'false',
                    'api_key' => $this->nasaApiKey
                ],
            ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new NasaApiParserException(sprintf(
                'Failed to get response from NASA API: %s HTTP status code',
                $response->getStatusCode())
            );
        }

        return $response;
    }

}
