<?php

declare(strict_types=1);

namespace Tracker\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Tracker\Jira\Client;
use Tracker\Jira\DelegatingExtractor;
use Tracker\Jira\DryRunClient;
use Tracker\Jira\DryRunSyncedRepo;
use Tracker\Jira\Exception\IssueIdNotRecognizedException;
use Tracker\Jira\JsonSynchronizedWorkLogRepository;
use Tracker\Jira\RegexExtractor;
use Tracker\Jira\SynchronizedWorkLog;
use Tracker\Jira\SynchronizedWorkLogRepository;
use Tracker\Jira\Synchronizer;
use Tracker\TimeLog;
use Tracker\TimeLogId;

#[AsCommand(name: 'synchronize', description: 'Synchronizes time logs with Jira.')]
final class SynchronizeCommand extends AbstractCommand
{
    private SynchronizedWorkLogRepository $synchronizedWorkLogRepository;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->synchronizedWorkLogRepository = new JsonSynchronizedWorkLogRepository($this->config['working-directory'].'/synced.json');
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', mode: InputOption::VALUE_NONE, description: 'Do not send requests to Jira');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $synchronizer = $this->createSynchronizer($input);

        foreach ($this->tracker->timeLogs() as $timeLog) {
            $output->writeln(\sprintf('Synchronizing time log #%s', $timeLog->id));

            try {
                $synchronizer->synchronize($timeLog);
            } catch (IssueIdNotRecognizedException) {
                $output->writeln('Issue ID not recognized. Skipping.');
            } catch (ClientExceptionInterface $e) {
                $output->writeln('An error occurred:');
                $output->writeln($e->getResponse()->getContent(false));
            } catch (\Throwable $e) {
                $output->writeln(sprintf('An error [%s] occurred:', $e::class));
                $output->writeln($e->getMessage());

                return self::FAILURE;
            }
        }

        foreach ($this->getRemovedWorkLogs() as $removedTimeLog) {
            $output->writeln(\sprintf('Removing time log #%s', $removedTimeLog->timeLog));

            try {
                $synchronizer->remove($removedTimeLog);
            } catch (ClientExceptionInterface $e) {
                $output->writeln('An error occurred:');
                $output->writeln($e->getResponse()->getContent(false));
            } catch (\Throwable $e) {
                $output->writeln(sprintf('An error [%s] occurred:', $e::class));
                $output->writeln($e->getMessage());

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    private function createSynchronizer(InputInterface $input): Synchronizer
    {
        $extractor = new DelegatingExtractor(
            ...array_map(fn ($regex) => new RegexExtractor($regex), $this->config['jira-extractor'])
        );

        if ($input->getOption('dry-run')) {
            return new Synchronizer(
                $extractor,
                new DryRunSyncedRepo($this->synchronizedWorkLogRepository),
                new DryRunClient($this->logger),
                $this->logger,
            );
        }

        return new Synchronizer(
            $extractor,
            $this->synchronizedWorkLogRepository,
            new Client($this->config['jira-url'], $this->config['jira-user'], $this->config['jira-token']),
            $this->logger,
        );
    }

    /**
     * @return array<SynchronizedWorkLog>
     */
    private function getRemovedWorkLogs(): array
    {
        return array_filter(
            (array) $this->synchronizedWorkLogRepository->all(),
            fn (SynchronizedWorkLog $synchronizedWorkLog) => !$this->timeLogExists($synchronizedWorkLog->timeLog)
        );
    }

    private function timeLogExists(TimeLogId $id): bool
    {
        return !empty(array_filter(
            $this->tracker->timeLogs(),
            fn (TimeLog $timeLog) => $timeLog->id->equals($id)
        ));
    }
}
