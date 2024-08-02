<?php

declare(strict_types=1);

namespace Tracker\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tracker\Jira\Client;
use Tracker\Jira\DelegatingExtractor;
use Tracker\Jira\DryRunClient;
use Tracker\Jira\DryRunSyncedRepo;
use Tracker\Jira\Synchronizer;
use Tracker\Jira\JsonSynchronizedWorkLogRepository;
use Tracker\Jira\RegexExtractor;

#[AsCommand(name: 'synchronize', description: 'Synchronizes time logs with Jira.')]
final class SynchronizeCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addOption('dry-run', mode: InputOption::VALUE_NONE, description: 'Do not send requests to Jira');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $synchronizer = $this->createSynchronizer($input);

        $synchronizer->synchronize($this->tracker);

        return self::SUCCESS;
    }

    private function createSynchronizer(InputInterface $input): Synchronizer
    {
        $extractor = new DelegatingExtractor(
            ...array_map(fn ($regex) => new RegexExtractor($regex), $this->config['jira-extractor'])
        );

        $synchronizedWorkLogRepository = new JsonSynchronizedWorkLogRepository($this->config['working-directory'].'/synced.json');

        if ($input->getOption('dry-run')) {
            return new Synchronizer(
                new DryRunClient($this->logger),
                $extractor,
                new DryRunSyncedRepo($synchronizedWorkLogRepository),
            );
        }

        return new Synchronizer(
            new Client($this->config['jira-url'], $this->config['jira-user'], $this->config['jira-token']),
            $extractor,
            $synchronizedWorkLogRepository,
        );
    }
}
