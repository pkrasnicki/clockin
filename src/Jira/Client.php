<?php

declare(strict_types=1);

namespace Tracker\Jira;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tracker\Common\Period;

final class Client implements ClientInterface
{
    private const string DATETIME_FORMAT = 'Y-m-d\TH:i:s.vO';
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

    public function addWorkLog(IssueId $issue, Period $period): JiraId
    {
        $response = $this->httpClient->request(
            'POST',
            sprintf('%s/rest/api/2/issue/%s/worklog', $this->jiraHost, $issue),
            ['json' => [
                'started' => $period->start->format(self::DATETIME_FORMAT),
                'timeSpentSeconds' => $period->duration->seconds,
            ]]
        );

        $jiraId = $response->toArray()['id'] ?? null;

        if (null === $jiraId) {
            throw new \RuntimeException('Work log not created.');
        }

        return new JiraId($jiraId);
    }

    public function deleteWorkLog(JiraId $id, IssueId $issue): void
    {
        $this->httpClient->request(
            'DELETE',
            sprintf('%s/rest/api/2/issue/%s/worklog/%s', $this->jiraHost, $issue, $id),
        );
    }

    public function updateWorkLog(JiraId $id, IssueId $issue, Period $period): void
    {
        $this->httpClient->request(
            'PUT',
            sprintf('%s/rest/api/2/issue/%s/worklog/%s', $this->jiraHost, $issue, $id),
            ['json' => [
                'started' => $period->start->format(self::DATETIME_FORMAT),
                'timeSpentSeconds' => $period->duration->seconds,
            ]]
        );
    }
}
