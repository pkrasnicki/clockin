<?php

declare(strict_types=1);

namespace ClockIn\Console;

use Monolog\Handler\Handler;
use Monolog\LogRecord;
use Symfony\Component\Console\Output\OutputInterface;

final class LogHandler extends Handler
{
    public function __construct(private OutputInterface $output)
    {
    }

    public function isHandling(LogRecord $record): bool
    {
        return true;
    }

    public function handle(LogRecord $record): bool
    {
        $this->output->writeln(sprintf('[%s] %s', $record->level->name, $record->message));
        $this->output->write('{');
        $this->output->write($record->context);
        $this->output->writeln('}');

        return true;
    }
}
