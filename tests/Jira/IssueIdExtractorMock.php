<?php

declare(strict_types=1);

namespace Tests\Jira;

use Tracker\Jira\IssueId;
use Tracker\Jira\IssueIdExtractor;
use Tracker\TimeLog;

final class IssueIdExtractorMock implements IssueIdExtractor
{
    private IssueId $willExtract;

    public function extract(TimeLog $timeLog): IssueId
    {
        return $this->willExtract;
    }

    public function willExtract(IssueId $issueId): self
    {
        $this->willExtract = $issueId;

        return $this;
    }
}
