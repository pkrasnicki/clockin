<?php

declare(strict_types=1);

namespace Tracker\Jira;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tracker\Common\Period;

final class Client
{
    const string DATETIME_FORMAT = 'Y-m-d\TH:i:s.vO';
    private HttpClientInterface $httpClient;

    public function __construct(
        private string $jiraHost,
        string $username,
        string $apiToken,
    ) {
        $this->jiraHost = rtrim($jiraHost, '/');
        $this->httpClient = HttpClient::create(['headers' => [
            'Authorization' => 'Basic '.base64_encode($username.':'.$apiToken),
        ]]);
    }

    public function addWorkLog(IssueId $issue, Period $period): WorkLogId
    {
        $response = $this->httpClient->request(
            'POST',
            sprintf('%s/rest/api/2/issue/%s/worklog', $this->jiraHost, $issue),
            ['json' => [
                'started' => $period->start->format(self::DATETIME_FORMAT),
                'timeSpentSeconds' => $period->duration->seconds,
            ]]
        );

        $workLogId = $response->toArray()['id'] ?? null;

        if (null === $workLogId) {
            throw new \RuntimeException('Work log not created.');
        }

        return new WorkLogId($workLogId);
    }

    public function updateWorkLog(WorkLogId $id, IssueId $issue, Period $period): void
    {
        echo PHP_EOL.'Updating work log '.$id.' for issue '.$issue.'...'.PHP_EOL;
    }
}
