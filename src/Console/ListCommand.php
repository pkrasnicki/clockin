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
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = (new Table($output))
               ->setHeaders(['ID', 'Description', 'Start', 'End', 'Duration']);

        foreach ($this->tracker->timeLogs() as $timeLog) {
            $table->addRow([
                $timeLog->id,
                $timeLog->description,
                $timeLog->period->start->format(self::DATE_FORMAT),
                $timeLog->period->end->format(self::DATE_FORMAT),
                $timeLog->duration(),
            ]);
        }

        if ($this->tracker->isRunning()) {
            $table->addRow([
                'N/A',
                $this->tracker->current()->description,
                $this->tracker->current()->start->format(self::DATE_FORMAT),
                'N/A',
                $this->tracker->current()->duration(),
            ]);
        }

        $table->render();

        return self::SUCCESS;
    }
}
