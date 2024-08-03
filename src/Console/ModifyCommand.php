<?php

declare(strict_types=1);

namespace ClockIn\Console;

use ClockIn\Tracker\TimeLog;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'modify',
    description: 'Modify the Time Log',
)]
final class ModifyCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addArgument('id', InputOption::VALUE_REQUIRED, 'The id of the task');
        $this->addOption('description', 'd', InputOption::VALUE_OPTIONAL, 'The description of the task');
        $this->addOption('start', 's', InputOption::VALUE_OPTIONAL, 'The start time of the task');
        $this->addOption('end', 'e', InputOption::VALUE_OPTIONAL, 'The end time of the task');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timeLog = $this->getTimeLog($input);

        if ($input->getOption('description')) {
            $this->tracker->updateDescription($timeLog->id, $input->getOption('description'));
        }

        $period = $timeLog->period;
        if ($input->getOption('start')) {
            $period = $period->withStart(new \DateTimeImmutable($input->getOption('start')));
        }
        if ($input->getOption('end')) {
            $period = $period->withEnd(new \DateTimeImmutable($input->getOption('end')));
        }

        $this->tracker->modifyPeriod($timeLog->id, $period);

        return self::SUCCESS;
    }

    private function getTimeLog(InputInterface $input): TimeLog
    {
        $timeLogs = collect($this->tracker->timeLogs())
            ->filter(fn (TimeLog $timeLog) => str_starts_with((string) $timeLog->id, $input->getArgument('id')));

        if ($timeLogs->isEmpty()) {
            throw new \InvalidArgumentException('Time log not found');
        }

        if ($timeLogs->count() > 1) {
            throw new \InvalidArgumentException('Multiple time logs found');
        }

        return $timeLogs->first();
    }
}
