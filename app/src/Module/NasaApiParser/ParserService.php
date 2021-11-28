<?php

namespace App\Module\NasaApiParser;

use App\DTO\NearEarthObjectDTO;
use App\Entity\NearEarthObject;
use App\Module\NasaApiParser\Exception\NasaApiException;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ParserService
{
    private const CACHE_NEO_GET_LIST_RESPONSE = 'nasa_api_neo_get_list_response';

    private EntityManagerInterface $entityManager;

    private AdapterInterface $cache;

    private ManagerRegistry $doctrine;

    private ValidatorInterface $validator;

    private ApiClient $apiClient;

    public function __construct(
        ApiClient $apiClient,
        EntityManagerInterface $entityManager,
        AdapterInterface $cache,
        ManagerRegistry $doctrine
    ) {
        $this->entityManager = $entityManager;
        $this->cache = $cache;
        $this->doctrine = $doctrine;
        $this->validator = Validation::createValidator();
        $this->apiClient = $apiClient;
    }

    public function processNearEarthObjectList(DateTimeImmutable $startDate = null)
    {
        $startDate = $startDate ?? new DateTimeImmutable('-3 days');
        $responseContent = $this->cache->getItem(self::CACHE_NEO_GET_LIST_RESPONSE);

        if (!$responseContent->isHit()) {
            $apiResponse = $this->apiClient->listNearEarthObjects($startDate);
            $responseContent->set($apiResponse);
            $responseContent->expiresAt(new DateTimeImmutable('tomorrow'));

            $this->cache->save($responseContent);
        }

        //TODO: move this on the 'no cache' step
        $this->parseNearEarthObjectListResponse($responseContent->get());

        return $responseContent;
    }

    private function parseNearEarthObjectListResponse(string $responseContent)
    {
        //TODO: add handling failed json decoding
        $decodedContent = json_decode($responseContent, true);

        $nearEarthObjects = $decodedContent['near_earth_objects'];

        foreach ($nearEarthObjects as $date => $groupedObjectsByDate) {
            foreach ($groupedObjectsByDate as $index => $singleObject) {
                $dto = $this->createNearEarthObjectDtoFromRawData($date, $singleObject);

                //TODO: add exception handling
                if ($this->isNearEarthObjectValid($dto)) {
                    $this->saveNearEarthObject($dto);
                }
            }
        }
    }

    private function saveNearEarthObject(NearEarthObjectDTO $dto): bool
    {
        $nearEarthObject = NearEarthObject::createFromDTO($dto);

        //TODO: refactor this to avoid resetting and reopening EM
        try {
            $this->entityManager->persist($nearEarthObject);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            //need to manually reset manager to continue insert new rows to the database
            if (!$this->entityManager->isOpen()) {
                $this->doctrine->resetManager();
            }
        }

        return true;
    }

    private function isNearEarthObjectValid(NearEarthObjectDTO $dto): bool
    {
        $violations = $this->validator->validate($dto);
        $validationErrorMessages = [];

        if (count($violations) !== 0) {
            foreach ($violations as $violation) {
                $validationErrorMessages[] = $violation->getMessage();
            }

            throw new NasaApiException(
                sprintf(
                    'Validation errors occured while processing Near Earth Objects: %s',
                    json_encode($validationErrorMessages)
                )
            );
        }

        return true;
    }

    private function createNearEarthObjectDtoFromRawData(string $date, array $rawData): NearEarthObjectDTO
    {
        //TODO: check keys in $rawData
        $dto = new NearEarthObjectDTO(
            DateTimeImmutable::createFromFormat('Y-m-d', $date),
            $rawData['neo_reference_id'],
            $rawData['name'],
            $rawData['close_approach_data'][0]['relative_velocity']['kilometers_per_hour'],
            $rawData['is_potentially_hazardous_asteroid']
        );

        return $dto;
    }
}
