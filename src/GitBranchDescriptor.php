<?php

declare(strict_types=1);

namespace Tracker;

final class GitBranchDescriptor implements TimeLogDescriptor
{
    public function getDescription(): string
    {
        $branch = exec('git branch --show-current');

        return $branch ?: '';
    }
}
