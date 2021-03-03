<?php

namespace App\Console;

use App\Entity\NearEarthObject;
use App\Module\NasaApiParser\Exception\NasaApiParserException;
use App\Module\NasaApiParser\ParserService;
use App\Repository\NearEarthObjectRepository;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NasaApiNearEarthObjectParser extends Command
{
    protected static $defaultName = 'nasa-api:parse-near-earth-objects';

    private $entityManager;

    private ParserService $parserService;

    public function __construct(
        $name = null,
        ParserService $parserService
    ) {
        parent::__construct($name);
        $this->parserService = $parserService;
    }

    protected function configure()
    {
        $description = 'Retrieves list of NEO over the past three days'
            .' from NASA API and saves to db.';
        $this
            ->setDescription($description);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Getting Near Earth Objects from NASA API');

        try {
            $response = $this->parserService->getNearEarthObjectsForThreeLastDays();
        } catch (NasaApiParserException $e) {
            $output->writeln("<error>" . $e->getMessage() . "</error>");

            return Command::FAILURE;
        }

        $decodedContent = json_decode($response->getBody()->getContents(), true);

        $nearEarthObjects = $decodedContent['near_earth_objects'];
        /**
         * @var NearEarthObjectRepository $NearEarthObjectRepository
         */
//        $NearEarthObjectRepository = $this->entityManager->getRepository(NearEarthObject::class);
//
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

        $output->writeln('Finished parsing Near Earth Objects from NASA API');

        return Command::SUCCESS;
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