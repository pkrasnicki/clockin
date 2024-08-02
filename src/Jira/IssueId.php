<?php

declare(strict_types=1);

namespace ClockIn\Jira;

use ClockIn\Common\UuidId;

final class IssueId extends UuidId implements \Stringable
{
    public function equals(IssueId $id): bool
    {
        return (string) $this === (string) $id;
    }
}
