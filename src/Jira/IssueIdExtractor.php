<?php

declare(strict_types=1);

namespace Tracker\Jira;

use Tracker\Jira\Exception\IssueIdNotRecognizedException;
use Tracker\TimeLog;

final class IssueIdExtractor
{
    public static function extractIssueId(TimeLog $timeLog): IssueId
    {
        preg_match('/SBX-\d{1,5}/', $timeLog->description, $matches);

        if (null === ($matches[0] ?? null)) {
            throw new IssueIdNotRecognizedException();
        }

        return new IssueId($matches[0]);
    }
}
