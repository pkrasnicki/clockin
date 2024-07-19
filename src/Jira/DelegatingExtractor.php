<?php

declare(strict_types=1);

namespace Tracker\Jira;

use Tracker\Jira\Exception\IssueIdNotRecognizedException;
use Tracker\TimeLog;

final class DelegatingExtractor implements IssueIdExtractor
{
    private array $extractors;

    public function __construct(IssueIdExtractor ...$issueIdExtractor)
    {
        $this->extractors = $issueIdExtractor;
    }

    public function extract(TimeLog $timeLog): IssueId
    {
        foreach ($this->extractors as $extractor) {
            try {
                return $extractor->extract($timeLog);
            } catch (IssueIdNotRecognizedException) {
                continue;
            }
        }

        throw new IssueIdNotRecognizedException();
    }
}
