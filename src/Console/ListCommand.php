<?php

declare(strict_types=1);

namespace Tracker\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'list', description: 'Lists all time logs.')]
final class ListCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = (new Table($output))
               ->setHeaders(['Description', 'Start', 'End', 'Duration']);

        foreach ($this->tracker->timeLogs() as $timeLog) {
            $table->addRow([
                $timeLog->description,
                $timeLog->period->start->format('Y-m-d H:i:s'),
                $timeLog->period->end->format('Y-m-d H:i:s'),
                $timeLog->duration(),
            ]);
        }

        if ($this->tracker->isRunning()) {
            $table->addRow([
                $this->tracker->current()->description,
                $this->tracker->current()->start->format('Y-m-d H:i:s'),
                'N/A',
                $this->tracker->current()->duration(),
            ]);
        }

        $table->render();

        return self::SUCCESS;
    }
}
