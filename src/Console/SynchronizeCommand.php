<?php

declare(strict_types=1);

namespace Tracker\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Tracker\Jira\Exception\IssueIdNotRecognizedException;
use Tracker\Jira\Synchronizer;

#[AsCommand(name: 'synchronize', description: 'Synchronizes time logs with Jira.')]
final class SynchronizeCommand extends AbstractCommand
{
    public function __construct(
        private Synchronizer $synchronizer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->tracker->timeLogs() as $timeLog) {
            $output->writeln(\sprintf('Synchronizing time log #%s', $timeLog->id));

            try {
                $this->synchronizer->synchronize($timeLog);
            } catch (IssueIdNotRecognizedException) {
                $output->writeln('Issue ID not recognized. Skipping.');
            } catch (ClientExceptionInterface $e) {
                $output->writeln('An error occurred:');
                $output->writeln($e->getResponse()->getContent(false));
            } catch (\Throwable $e) {
                $output->writeln(sprintf('An error [%s] occurred:', $e::class));
                $output->writeln($e->getMessage());

                var_dump($e);

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
