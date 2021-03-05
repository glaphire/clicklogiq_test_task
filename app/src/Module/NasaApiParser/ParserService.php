<?php

namespace App\Module\NasaApiParser;

use App\DTO\NearEarthObjectDTO;
use App\Entity\NearEarthObject;
use App\Module\NasaApiParser\Exception\NasaApiParserException;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validation;

class ParserService
{
    private const NASA_API_BASE_URL = 'https://api.nasa.gov';

    private const CACHE_KEY_RESPONSE_CONTENT = 'nasa_api_response_content';

    private Client $httpClient;

    private string $nasaApiKey;

    private EntityManagerInterface $entityManager;

    private AdapterInterface $cache;

    private ManagerRegistry $doctrine;

    public function __construct(Client $httpClient,
                                EntityManagerInterface $entityManager,
                                AdapterInterface $cache,
                                ManagerRegistry $doctrine,
                                string $nasaApiKey
    ) {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->cache = $cache;
        $this->nasaApiKey = $nasaApiKey;
        $this->doctrine = $doctrine;
    }

    public function getNearEarthObjectsForThreeLastDays()
    {
        $responseContent = $this->cache->getItem(self::CACHE_KEY_RESPONSE_CONTENT);

        if (!$responseContent->isHit()) {

            $responseContent->set($this->getNearEarthObjectsFromNasa());
            $responseContent->expiresAt(new DateTimeImmutable('tomorrow'));

            $this->cache->save($responseContent);
        }

        $this->parseNearEarthObjectsFromResponse($responseContent->get());

        return $responseContent;
    }

    private function parseNearEarthObjectsFromResponse(string $responseContent)
    {
        $decodedContent = json_decode($responseContent, true);

        $validator = Validation::createValidator();

        $nearEarthObjects = $decodedContent['near_earth_objects'];

        foreach ($nearEarthObjects as $date => $groupedObjectsByDate) {
            {
                foreach ($groupedObjectsByDate as $index => $singleObject) {

                    $dto = new NearEarthObjectDTO(
                        DateTimeImmutable::createFromFormat('Y-m-d', $date),
                        $singleObject['neo_reference_id'],
                        $singleObject['name'],
                        $singleObject['close_approach_data'][0]['relative_velocity']['kilometers_per_hour'],
                        $singleObject['is_potentially_hazardous_asteroid']
                    );

                    $violations = $validator->validate($dto);

                    if (count($violations) !== 0) {
                        foreach ($violations as $violation) {
                            throw new NasaApiParserException('Validation error occured: ' . $violation->getMessage);
                        }
                    }

                    $this->saveNearEarthObject($dto);
                }
            }
        }
    }

    private function getNearEarthObjectsFromNasa(): string
    {
        $endDate = new DateTimeImmutable();
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

        return $response->getBody()->getContents();
    }

    private function saveNearEarthObject(NearEarthObjectDTO $dto): bool
    {
        $nearEarthObject = new NearEarthObject();
        $nearEarthObject
            ->setDate($dto->date)
            ->setReference($dto->reference)
            ->setName($dto->name)
            ->setSpeed($dto->speed)
            ->setIsHazardous($dto->isHazardous)
        ;

        try {
            $this->entityManager->persist($nearEarthObject);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            //need to manually reset manager to continue insert new rows to database
            if (!$this->entityManager->isOpen()) {
                $this->doctrine->resetManager();
            }
        }

        return true;
    }
}
