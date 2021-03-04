<?php

namespace App\Module\NasaApiParser;

use App\DTO\NearEarthObjectDTO;
use App\Entity\NearEarthObject;
use App\Module\NasaApiParser\Exception\NasaApiParserException;
use App\Repository\NearEarthObjectRepository;
use DateTimeImmutable;
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

    public function __construct(Client $httpClient,
                                EntityManagerInterface $entityManager,
                                AdapterInterface $cache,
                                string $nasaApiKey
    ) {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->cache = $cache;
        $this->nasaApiKey = $nasaApiKey;
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
                        new \DateTime(),
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
                }
            }
        }

//        /**
//         * @var NearEarthObjectRepository $NearEarthObjectRepository
//         */
//        $NearEarthObjectRepository = $this->entityManager->getRepository(NearEarthObject::class);

//        foreach ($nearEarthObjects as $date => $groupedByDate) {
//            foreach ($groupedByDate as $i => $singleNearEarthObject) {
//                if ($NearEarthObjectRepository->findOneReference($singleNearEarthObject['neo_reference_id'])) {
//                    continue;
//                }
//
//                $this->saveNearEarthObject($singleNearEarthObject, $date);
//            }
//        }
//
//        $this->entityManager->flush();
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
}
