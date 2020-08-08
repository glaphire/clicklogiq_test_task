<?php

namespace App\Console;

use App\Entity\NearEarthObject;
use App\Repository\NearEarthObjectRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

class NasaApiNearEarthObjectParser extends Command
{
    protected static $defaultName = 'nasa-api:parse-near-earth-objects';

    private Client $httpClient;

    private string $nasaApiKey;

    private $entityManager;

    public function __construct(
        $name = null,
        Client $httpClient,
        string $nasaApiKey,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($name);
        $this->httpClient = $httpClient;
        $this->nasaApiKey = $nasaApiKey;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Retrieves list of Near Earth Objects from NASA API and saves to db for last three days.');
            //->setHelp('This command allows you to create a user...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Getting Near Earth Objects from NASA API');

        $endDate = new DateTimeImmutable();
        $startDate = $endDate->modify('-3 days');

        $response = $this->httpClient->get('https://api.nasa.gov/neo/rest/v1/feed?',
            [
                'query' => [
                    'start_date' => $startDate->format('Y-m-d'), //TODO: create default and console argument date
                    'end_date' => $endDate->format('Y-m-d'), //TODO: create default and console argument date
                    'detailed' => 'false',
                    'api_key' => $this->nasaApiKey
                ],
            ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            //TODO: add logging

            $errorMessage = sprintf(
                'Failed to get response from NASA API: %s HTTP status code',
                $response->getStatusCode()
            );

            $output->writeln("<error>$errorMessage</error>");
            return Command::FAILURE;
        }

        $decodedContent = json_decode($response->getBody()->getContents(), true);

        $nearEarthObjects = $decodedContent['near_earth_objects'];
        /**
         * @var NearEarthObjectRepository $neoRepo
         */
        $neoRepo = $this->entityManager->getRepository(NearEarthObject::class);

        foreach ($nearEarthObjects as $date => $groupedByDate) {
            foreach ($groupedByDate as $i => $singleNearEarthObject) {
                if ($neoRepo->findOneReference($singleNearEarthObject['neo_reference_id'])) {
                    continue;
                }

                $nearEarthObjectEntity = new NearEarthObject();
                $nearEarthObjectEntity->setDate(new DateTime($date));
                $nearEarthObjectEntity->setReference($singleNearEarthObject['neo_reference_id']);
                $nearEarthObjectEntity->setName($singleNearEarthObject['name']);

                $nearEarthObjectEntity
                    ->setSpeed($singleNearEarthObject['close_approach_data'][0]['relative_velocity']['kilometers_per_hour']);
                $nearEarthObjectEntity->setIsHazardous($singleNearEarthObject['is_potentially_hazardous_asteroid']);
                $this->entityManager->persist($nearEarthObjectEntity);
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}