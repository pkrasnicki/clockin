<?php

declare(strict_types=1);

namespace ClockIn\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'current', description: 'Shows the current time log.')]
final class CurrentCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->tracker->isRunning()) {
            $output->writeln('Currently not tracking.');

            return Command::SUCCESS;
        }

        $current = $this->tracker->current();

        $output->writeln(sprintf('Current time log: %s (%s)', $current->description, $current->duration()));

        return Command::SUCCESS;
    }
}
