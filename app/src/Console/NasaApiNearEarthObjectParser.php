<?php

namespace App\Console;

use App\Module\NasaApiParser\Exception\NasaApiException;
use App\Module\NasaApiParser\ParserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

//TODO: add LoggerAwareInterface and logging
class NasaApiNearEarthObjectParser extends Command
{
    protected static $defaultName = 'nasa-api:parse-near-earth-objects';

    private ParserService $parserService;

    public function __construct(ParserService $parserService)
    {
        parent::__construct($name = null);
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
            $this->parserService->parseNearEarthObjectList();
        } catch (NasaApiException $e) {
            $output->writeln("<error>" . $e->getMessage() . "</error>");

            return Command::FAILURE;
        }

        $output->writeln('Finished parsing Near Earth Objects from NASA API');

        return Command::SUCCESS;
    }
}
