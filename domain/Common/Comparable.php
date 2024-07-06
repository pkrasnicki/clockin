<?php

namespace Tracker\Common;

/**
 * @template T
 */
interface Comparable
{
    /**
     * @param Comparable<T> $as
     */
    public function same(Comparable $as): bool;
}
