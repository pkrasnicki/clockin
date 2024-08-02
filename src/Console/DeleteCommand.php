<?php

declare(strict_types=1);

namespace ClockIn\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ClockIn\TimeLog;
use ClockIn\TimeLogId;

#[AsCommand(name: 'delete', description: 'Deletes a time log.')]
final class DeleteCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'The ID of the time log to delete.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $this->getTimeLogId($input);

        $output->writeln(sprintf('Deleting time log #%s', $id));
        $this->tracker->remove($id);

        return self::SUCCESS;
    }

    private function getTimeLogId(InputInterface $input): TimeLogId
    {
        $inputId = $input->getArgument('id');

        $ids = array_filter(
            $this->tracker->timeLogs(),
            fn (TimeLog $timeLog) => str_starts_with((string) $timeLog->id, $inputId)
        );

        if (0 === count($ids)) {
            throw new \InvalidArgumentException(sprintf('Time log with ID "%s" not found.', $inputId));
        }

        if (count($ids) > 1) {
            throw new \InvalidArgumentException(sprintf('Multiple time logs with ID "%s" found.', $inputId));
        }

        return array_values($ids)[0]->id;
    }
}
