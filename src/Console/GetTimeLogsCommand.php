<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracker\TimeLog\Repository as TimeLogRepository;

#[AsCommand(name: 'app:time-log:all', description: 'Get all time logs')]
class GetTimeLogsCommand extends Command
{
    public function __construct(private readonly TimeLogRepository $timeLogRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timeLogs = $this->timeLogRepository->all();

        (new Table($output))
            ->setHeaders(['ID', 'Start', 'Stop', 'Description'])
            ->setRows(array_map(
                function ($timeLog) {
                    return [
                        $timeLog->id,
                        $timeLog->timeSpan->start->format('Y-m-d H:i:s'),
                        $timeLog->timeSpan->end->format('Y-m-d H:i:s'),
                        $timeLog->description,
                    ];
                },
                $timeLogs,
            ))
            ->render();

        return Command::SUCCESS;
    }
}
