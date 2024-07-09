<?php

declare(strict_types=1);

namespace Tracker\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

final class Application extends SymfonyApplication
{
    public function __construct()
    {
        parent::__construct('Tracker', '0.0.1');
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption(
            '--file',
            '-f',
            InputOption::VALUE_OPTIONAL,
            'The file to store time logs in.',
        ));

        return $definition;
    }

    protected function getDefaultCommands(): array
    {
        return array_map(
            function (Command $command) {
                if ($command instanceof ListCommand) {
                    $command->setName('list-commands');
                    $this->setDefaultCommand('list-commands');
                }

                return $command;
            },
            parent::getDefaultCommands()
        );
    }
}
