<?php

declare(strict_types=1);

namespace ClockIn\Console;

use ClockIn\Tracker\JsonFileTrackerRepository;
use ClockIn\Tracker\Tracker;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\NativeClock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractCommand extends Command
{
    protected Tracker $tracker;
    protected array $config;
    protected LoggerInterface $logger;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->config = $this->loadConfiguration($input);

        Clock::set((new NativeClock())->withTimeZone($this->config['timezone']));

        $this->logger = new Logger('clockin');
        $this->logger->pushHandler(new LogHandler($output));

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
        $required = ['jira-url', 'jira-user', 'jira-token', 'jira-extractor', 'timezone'];

        $workingDirectory = $input->getOption('working-directory') ?:
            $_SERVER['CLOCKIN_WORKING_DIRECTORY'] ??
                $_SERVER['HOME'].'/.clockin';

        $configuration = array_intersect_key(
            array_merge(
                self::normalizedEnv(),
                self::normalizedYaml($workingDirectory.'/config.yaml'),
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

    private static function normalizedEnv(): array
    {
        $env = $_SERVER;
        $normalized = [];

        $env = array_filter($env, fn ($key) => str_starts_with($key, 'CLOCKIN_'), ARRAY_FILTER_USE_KEY);

        foreach ($env as $key => $value) {
            $key = str_replace('CLOCKIN_', '', $key);
            $key = str_replace('_', '-', strtolower($key));

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private static function normalizedYaml(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        try {
            $config = Yaml::parseFile($filePath);
        } catch (ParseException) {
            return [];
        }

        $normalized = [];

        foreach ($config as $key => $value) {
            if($key !== 'jira') {
                $normalized[$key] = $value;
            }
        }

        foreach ($config['jira'] as $key => $value) {
            $normalized['jira-'.$key] = $value;
        }

        return $normalized;
    }
}
