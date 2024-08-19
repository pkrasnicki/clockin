<?php

declare(strict_types=1);

namespace ClockIn\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

final class Application extends SymfonyApplication
{
    public function __construct()
    {
        parent::__construct('ClockIn', '0.0.5');
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption(
            '--working-directory',
            '-w',
            InputOption::VALUE_OPTIONAL,
            'Path to the directory where configuration and database is stored',
        ));

        $definition->addOption(new InputOption(
            '--jira-url',
            null,
            InputOption::VALUE_OPTIONAL,
        ));
        $definition->addOption(new InputOption(
            '--jira-user',
            null,
            InputOption::VALUE_OPTIONAL,
        ));
        $definition->addOption(new InputOption(
            '--jira-token',
            null,
            InputOption::VALUE_OPTIONAL,
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
