<?php

declare(strict_types=1);

namespace ClockIn\Console;

use ClockIn\Common\Duration;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'list', description: 'Lists all time logs.')]
final class ListCommand extends AbstractCommand
{
    private const string DATE_FORMAT = 'Y-m-d H:i:s';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = (new Table($output))
            ->setHeaders(['ID', 'Description', 'Start', 'End', 'Duration']);

        $lastDay = null;
        $duration = new Duration(0);
        foreach ($this->tracker->timeLogs() as $timeLog) {
            if ($lastDay !== null && $lastDay !== $timeLog->period->start->format('Y-m-d')) {
                $table->addRow([new TableCell('Summary', ['colspan' => 4, 'style'=> new TableCellStyle(['align' => 'right'])]), new TableCell((string)$duration)]);
                $table->addRow(new TableSeparator());
                $duration = new Duration(0);
            }

            $duration = $duration->add($timeLog->duration());

            $table->addRow([
                $timeLog->id,
                $timeLog->description,
                $timeLog->period->start->format(self::DATE_FORMAT),
                $timeLog->period->end->format(self::DATE_FORMAT),
                $timeLog->duration(),
            ]);

            $lastDay = $timeLog->period->start->format('Y-m-d');
        }

        if ($this->tracker->isRunning()) {
            $table->addRow([
                'N/A',
                $this->tracker->current()->description,
                $this->tracker->current()->start->format(self::DATE_FORMAT),
                'N/A',
                $this->tracker->current()->duration(),
            ]);

            $duration = $duration->add($this->tracker->current()->duration());
        }

        $table->addRow([new TableCell('Summary', ['colspan' => 4, 'style'=> new TableCellStyle(['align' => 'right'])]), new TableCell((string)$duration)]);

        $table->render();

        return self::SUCCESS;
    }
}
