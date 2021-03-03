<?php

namespace App\Module\NasaApiParser;

use App\Entity\NearEarthObject;
use App\Module\NasaApiParser\Exception\NasaApiParserException;
use App\Repository\NearEarthObjectRepository;
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

        $this->parseResponse($response);

        return $response;
    }

    private function parseResponse($response)
    {
        $decodedContent = json_decode($response->getBody()->getContents(), true);

        $nearEarthObjects = $decodedContent['near_earth_objects'];

        /**
         * @var NearEarthObjectRepository $NearEarthObjectRepository
         */
        $NearEarthObjectRepository = $this->entityManager->getRepository(NearEarthObject::class);

        foreach ($nearEarthObjects as $date => $groupedByDate) {
            foreach ($groupedByDate as $i => $singleNearEarthObject) {
                if ($NearEarthObjectRepository->findOneReference($singleNearEarthObject['neo_reference_id'])) {
                    continue;
                }

                $this->saveNearEarthObject($singleNearEarthObject, $date);
            }
        }

        $this->entityManager->flush();
    }

    //TODO: refactor to use Serializer (deserializer)
    private function saveNearEarthObject(array $nearEarthObjectRawArray, string $date)
    {
        $nearEarthObjectEntity = new NearEarthObject();
        $nearEarthObjectEntity->setDate(new DateTimeImmutable($date));
        $nearEarthObjectEntity->setReference($nearEarthObjectRawArray['neo_reference_id']);
        $nearEarthObjectEntity->setName($nearEarthObjectRawArray['name']);

        $nearEarthObjectEntity
            ->setSpeed($nearEarthObjectRawArray['close_approach_data'][0]['relative_velocity']['kilometers_per_hour']);
        $nearEarthObjectEntity->setIsHazardous($nearEarthObjectRawArray['is_potentially_hazardous_asteroid']);
        $this->entityManager->persist($nearEarthObjectEntity);
    }

}
