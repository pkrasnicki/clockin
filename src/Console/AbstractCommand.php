<?php

declare(strict_types=1);

namespace Tracker\Console;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Tracker\Repository\JsonFileTrackerRepository;
use Tracker\Tracker;

abstract class AbstractCommand extends Command
{
    protected Tracker $tracker;
    protected array $config;
    protected LoggerInterface $logger;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->logger = new Logger('tracker');
        $this->logger->pushHandler(new LogHandler($output));

        $this->config = $this->loadConfiguration($input);

        $trackerRepository = new JsonFileTrackerRepository($this->config['working-directory'].'/logs.json');
        $this->tracker = $trackerRepository->load();

        $this->setCode(function (InputInterface $input, OutputInterface $output) use ($trackerRepository) {
            $result = $this->execute($input, $output);
            $trackerRepository->save($this->tracker);

            return $result;
        });
    }

    private function loadConfiguration(InputInterface $input): array
    {
        $required = ['jira-url', 'jira-user', 'jira-token'];

        $workingDirectory = $input->getOption('working-directory') ?:
            $_SERVER['TRACKER_WORKING_DIRECTORY'] ??
                $_SERVER['HOME'].'/.tracker';

        $configuration = array_intersect_key(
            array_merge(
                self::normalizeEnv($_SERVER),
                file_exists($workingDirectory.'/config.yaml') ? self::flatten(Yaml::parseFile($workingDirectory.'/config.yaml')) : [],
                array_filter($input->getOptions()),
            ),
            array_fill_keys($required, null),
        );

        if (count($required) !== count(array_filter($configuration))) {
            throw new \InvalidArgumentException(sprintf('Missing configuration options: %s', implode(array_diff($required, array_keys(array_filter($configuration))))));
        }

        $configuration['working-directory'] = $workingDirectory;

        return $configuration;
    }

    private static function normalizeEnv(array $env): array
    {
        $normalized = [];

        $env = array_filter($env, fn ($key) => str_starts_with($key, 'TRACKER_'), ARRAY_FILTER_USE_KEY);

        foreach ($env as $key => $value) {
            $key = str_replace('TRACKER_', '', $key);
            $key = str_replace('_', '-', strtolower($key));

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private static function flatten(array $array, string $prefix = ''): array
    {
        $return = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $return = array_merge($return, self::flatten($value, $prefix.$key.'-'));
            } else {
                $return[$prefix.$key] = $value;
            }
        }

        return $return;
    }
}
