<?php

declare(strict_types=1);

namespace ClockIn\Jira;

use ClockIn\Jira\Exception\IssueIdNotRecognizedException;
use ClockIn\Tracker\TimeLog;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

final class InteractiveIssueIdExtractor implements IssueIdExtractor
{
    public function __construct(private InputInterface $input, private OutputInterface $output)
    {
    }

    public function extract(TimeLog $timeLog): IssueId
    {
        $this->output->writeln('Could not recognize an Issue ID for Time Log');
        $this->output->writeln(sprintf('[%s] "%s"', $timeLog->id, $timeLog->description));
        $question = new Question('Enter an issue ID: ');

        $userInput = (new QuestionHelper())->ask($this->input, $this->output, $question);

        if (null === $userInput) {
            throw new IssueIdNotRecognizedException();
        }

        return new IssueId($userInput);
    }
}
