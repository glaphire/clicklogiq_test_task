<?php

namespace App\Console;

use App\Module\NasaApiParser\Exception\NasaApiException;
use App\Module\NasaApiParser\ParserService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NasaApiNearEarthObjectParser extends Command implements LoggerAwareInterface
{
    protected static $defaultName = 'nasa-api:parse-near-earth-objects';

    private ParserService $parserService;

    private LoggerInterface $logger;

    public function __construct(ParserService $parserService, LoggerInterface $logger)
    {
        parent::__construct($name = null);

        $this->parserService = $parserService;
        $this->logger = $logger;
    }


    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $description = 'Retrieves list of Near Earth Objects over the past three days'
            .' from NASA API and saves to Database.';

        $this->setDescription($description);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Getting Near Earth Objects from NASA API');

        try {
            $this->parserService->processNearEarthObjectList();
        } catch (NasaApiException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            //TODO: refactor to use "error" method
            $this->logger->log(LogLevel::ERROR, $e->getMessage());

            return Command::FAILURE;
        }

        $output->writeln('Finished parsing Near Earth Objects from NASA API');

        return Command::SUCCESS;
    }
}
