<?php

namespace App\Infrastructure\Command;

use App\Domain\Exception\CreateEventNotFoundException;
use App\Domain\Exception\ParserException;
use App\UseCase\CreateEventUseCase;
use App\UseCase\ParserUseCase;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddEventCommand extends Command
{
    private const SINCE_DAYS = 1;

    protected static $defaultName = 'add:event';

    public function __construct(
        private ParserUseCase $parserUseCase,
        private CreateEventUseCase $createEventUseCase,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'brands',
                mode: InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                description: 'Brands we ordered to',
            )
            ->addOption(
                name: 'since',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Since '.self::SINCE_DAYS.' days',
                default: self::SINCE_DAYS,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sinceDay = $input->getOption('since');
        $since = (new DateTimeImmutable())->modify("-$sinceDay days");

        $brands = $input->getArgument('brands');

        try {
            $parsedEvents = $this->parserUseCase->execute($brands, $since);
            $this->createEventUseCase->execute($parsedEvents);
        } catch (ParserException | CreateEventNotFoundException $e) {
            $output->writeln($e->getMessage());
        }

        return Command::SUCCESS;
    }
}
