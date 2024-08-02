<?php

declare(strict_types=1);

namespace Tests\Jira;

use Illuminate\Support\Collection;
use ClockIn\Common\Period;
use ClockIn\Jira\ClientInterface;
use ClockIn\Jira\IssueId;
use ClockIn\Jira\JiraId;

final class JiraClientSpy implements ClientInterface
{
    public function __construct(
        public Collection $added = new Collection(),
        public Collection $deleted = new Collection(),
        public Collection $updated = new Collection(),
    ) {
    }

    public function addWorkLog(IssueId $issue, Period $period): JiraId
    {
        static $jiraIdEnumerator = 1;
        $jiraId = new JiraId((string) ($jiraIdEnumerator++));

        $this->added->add([
            'issue' => $issue,
            'period' => $period,
        ]);

        return $jiraId;
    }

    public function deleteWorkLog(JiraId $id, IssueId $issue): void
    {
        $this->deleted->add([
            'jiraId' => $id,
            'issue' => $issue,
        ]);
    }

    public function updateWorkLog(JiraId $id, IssueId $issue, Period $period): void
    {
        $this->updated->add([
            'jiraId' => $id,
            'issue' => $issue,
            'period' => $period,
        ]);
    }

    public function clear(): void
    {
        $this->added = new Collection();
        $this->updated = new Collection();
        $this->deleted = new Collection();
    }
}
