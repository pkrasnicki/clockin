<?php
declare(strict_types=1);

namespace ClockIn\Console;

use ClockIn\Common\Duration;
use ClockIn\Tracker\Descriptor\CompositeDescriptor;
use ClockIn\Tracker\Descriptor\GitBranchDescriptor;
use ClockIn\Tracker\Descriptor\StringDescriptor;
use ClockIn\Tracker\TimeLog;
use ClockIn\Tracker\TimeLogId;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'sum-up', description: 'Get summed up entry time')]
class SumUpCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addArgument('description', InputArgument::OPTIONAL, 'The description of the time log.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $description = (new CompositeDescriptor(
                new StringDescriptor($input->getArgument('description') ?: ''),
                new GitBranchDescriptor()
            ))->getDescription();
        } catch (\RuntimeException $e) {
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        $seconds = collect($this->tracker->timeLogs())
            ->filter(fn(TimeLog $tl) => $tl->description === $description)
            ->sum(fn(TimeLog $tl) => $tl->duration()->seconds);

        $output->writeln(sprintf('Total time for "%s" is %s', $description, new Duration($seconds)));

        return Command::SUCCESS;
    }
}