<?php

declare(strict_types=1);

namespace ClockIn\Descriptor;

final class GitBranchDescriptor implements TimeLogDescriptor
{
    public function getDescription(): string
    {
        $branch = exec('git branch --show-current');

        return $branch ?: '';
    }
}
