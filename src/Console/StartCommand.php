<?php

declare(strict_types=1);

namespace Tracker\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'start', description: 'Starts tracking time.')]
class StartCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addArgument('description', InputArgument::REQUIRED, 'The description of the time log.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->tracker->start($input->getArgument('description'));

        return Command::SUCCESS;
    }
}
