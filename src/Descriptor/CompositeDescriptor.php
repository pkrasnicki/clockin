<?php

declare(strict_types=1);

namespace Tracker;

final class CompositeDescriptor implements TimeLogDescriptor
{
    private array $extractors;

    public function __construct(TimeLogDescriptor ...$extractors)
    {
        $this->extractors = $extractors;
    }

    public function getDescription(): string
    {
        foreach ($this->extractors as $extractor) {
            $description = $extractor->getDescription();
            if ('' !== $description) {
                return $description;
            }
        }

        throw new \RuntimeException('No description found.');
    }
}
