<?php

declare(strict_types=1);

namespace ClockIn\Jira;

use ClockIn\Common\Period;
use Psr\Log\LoggerInterface;

final class DryRunClient implements ClientInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function addWorkLog(IssueId $issue, Period $period): JiraId
    {
        $this->logger->info('Client.add', ['issueId' => $issue]);

        return new JiraId((string) random_int(10_000, 99_999));
    }

    public function deleteWorkLog(JiraId $id, IssueId $issue): void
    {
        $this->logger->info('Client.delete', ['issueId' => $issue]);
    }

    public function updateWorkLog(JiraId $id, IssueId $issue, Period $period): void
    {
        $this->logger->info('Client.update', ['issueId' => $issue]);
    }
}
