<?php

namespace App\Module\NasaApiParser;

use App\DTO\NearEarthObjectDTO;
use App\Module\NasaApiParser\Exception\NasaApiParserException;
use DateTimeImmutable;
use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ParserServiceRefactored
{
    const CACHE_KEY_RESPONSE_CONTENT = 'nasa_api_response';
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var Client
     */
    private $client;

    private const NASA_API_BASE_URL = 'https://api.nasa.gov';
    /**
     * @var AdapterInterface
     */
    private $cache;

    public function __construct(SerializerInterface $serializer, Client $client, AdapterInterface $cache)
    {
        $this->serializer = $serializer;
        $this->client = $client;
        $this->cache = $cache;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getApiResponse()
    {
        $endDate = new DateTimeImmutable();
        $startDate = $endDate->modify('-3 days');

        $response = $this->client->get(self::NASA_API_BASE_URL . '/neo/rest/v1/feed',
            [
                'query' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'detailed' => 'false',
                    'api_key' => 'N7LkblDsc5aen05FJqBQ8wU4qSdmsftwJagVK7UD',
                ],
            ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new NasaApiParserException(sprintf(
                    'Failed to get response from NASA API: %s HTTP status code',
                    $response->getStatusCode())
            );
        }

        return $response->getBody()->getContents();
    }

    public function parseApiResponse() //:NearEarthObjects[]
    {
        $responseContent = $this->cache->getItem(self::CACHE_KEY_RESPONSE_CONTENT);

        if (!$responseContent->isHit()) {

            $responseContent->set($this->getApiResponse());
            $responseContent->expiresAt(new DateTimeImmutable('tomorrow'));

            $this->cache->save($responseContent);
        }
        var_dump(22222);
        //die();
        $data = $responseContent->get();

       // $result = $this->serializer->deserialize($data, NearEarthObjectDTO::class, 'json');

        //return $responseContent;
    }

    public function saveToDb(): bool
    {

    }
}
