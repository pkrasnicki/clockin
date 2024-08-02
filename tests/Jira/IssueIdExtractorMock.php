<?php

declare(strict_types=1);

namespace Tests\Jira;

use ClockIn\Jira\IssueId;
use ClockIn\Jira\IssueIdExtractor;
use ClockIn\TimeLog;

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
