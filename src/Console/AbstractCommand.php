<?php

declare(strict_types=1);

namespace Tracker\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracker\Repository\JsonFileTrackerRepository;
use Tracker\Tracker;

abstract class AbstractCommand extends Command
{
    protected Tracker $tracker;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $trackerRepository = new JsonFileTrackerRepository(__DIR__.'/../../var/data.json');
        $this->tracker = $trackerRepository->load();

        $this->setCode(function (InputInterface $input, OutputInterface $output) use ($trackerRepository) {
            $result = $this->execute($input, $output);
            $trackerRepository->save($this->tracker);

            return $result;
        });
    }
}
