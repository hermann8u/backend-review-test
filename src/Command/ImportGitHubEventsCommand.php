<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Event;
use App\Github\GhArchive\Client\GhArchiveClientInterface;
use App\Repository\WriteEventRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This command must import GitHub events.
 * You can add the parameters and code you want in this command to meet the need.
 */
#[AsCommand(
    name: 'app:import-github-events',
    description: 'Import GH events',
)]
class ImportGitHubEventsCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly GhArchiveClientInterface $client,
        private readonly WriteEventRepository $writeEventRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('date_hour', InputArgument::REQUIRED, 'The date and hour for the events to import with the format Y-m-d-H')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateHour = $input->getArgument('date_hour');
        $dateTime = \DateTimeImmutable::createFromFormat('!Y-m-d-H', $dateHour);

        $events = $this->client->getEvents($dateTime);

        $count = 0;

        /** @var Event $event */
        foreach ($this->io->progressIterate($events) as $event) {
            $this->writeEventRepository->add($event);

            $count++;
        }

        $this->io->success(sprintf('%d events successfully imported', $count));

        return Command::SUCCESS;
    }
}
