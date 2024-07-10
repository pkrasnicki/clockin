<?php

declare(strict_types=1);

namespace Tracker\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracker\Descriptor\CompositeDescriptor;
use Tracker\Descriptor\GitBranchDescriptor;
use Tracker\Descriptor\StringDescriptor;

#[AsCommand(name: 'start', description: 'Starts tracking time.')]
final class StartCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addArgument('description', InputArgument::OPTIONAL, 'The description of the time log.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->tracker->isRunning()) {
            $output->writeln(sprintf(
                'Stopping "%s" (%s).',
                $this->tracker->current()->description,
                $this->tracker->current()->duration()
            ));
            $this->tracker->stop();
        }

        try {
            $description = (new CompositeDescriptor(
                new StringDescriptor($input->getArgument('description') ?: ''),
                new GitBranchDescriptor()
            ))->getDescription();
        } catch (\RuntimeException $e) {
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        $output->writeln(sprintf('Starting "%s".', $description));
        $this->tracker->start($description);

        return Command::SUCCESS;
    }
}
