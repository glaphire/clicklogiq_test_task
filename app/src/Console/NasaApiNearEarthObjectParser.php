<?php

namespace App\Console;

use DateTimeImmutable;
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

    public function __construct($name = null, Client $httpClient, string $nasaApiKey)
    {
        parent::__construct($name);
        $this->httpClient = $httpClient;
        $this->nasaApiKey = $nasaApiKey;
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
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}