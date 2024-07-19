<?php

declare(strict_types=1);

namespace Tracker\Jira;

use Tracker\Jira\Exception\IssueIdNotRecognizedException;
use Tracker\TimeLog;

final class RegexExtractor implements IssueIdExtractor
{
    public function __construct(private string $regex)
    {
    }

    public function extract(TimeLog $timeLog): IssueId
    {
        preg_match('/'.$this->regex.'/', $timeLog->description, $matches);

        if (null === ($matches[0] ?? null)) {
            throw new IssueIdNotRecognizedException();
        }

        return new IssueId($matches[0]);
    }
}
