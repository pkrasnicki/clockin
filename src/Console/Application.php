<?php

declare(strict_types=1);

namespace Tracker\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\ListCommand;

final class Application extends SymfonyApplication
{
    public function __construct()
    {
        parent::__construct('Tracker', '0.0.1');
    }

    protected function getDefaultCommands(): array
    {
        return array_map(
            function (Command $command) {
                if ($command instanceof ListCommand) {
                    $command->setName('list-commands');
                }

                return $command;
            },
            parent::getDefaultCommands()
        );
    }
}
