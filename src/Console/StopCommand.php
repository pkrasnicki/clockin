<?php

declare(strict_types=1);

namespace Tracker\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'stop', description: 'Stops tracking time.')]
final class StopCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->tracker->isRunning()) {
            $output->writeln('Tracker is not running.');

            return self::INVALID;
        }

        $output->writeln(sprintf(
            'Stopping "%s" (%s).',
            $this->tracker->current()->description,
            $this->tracker->current()->duration()
        ));

        $this->tracker->stop();

        return Command::SUCCESS;
    }
}
