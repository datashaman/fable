<?php

namespace App\Exceptions;

use RuntimeException;

class StaleRevisionException extends RuntimeException
{
    public function __construct(int $expected, int $actual)
    {
        parent::__construct("Stale revision: expected {$expected}, current revision is {$actual}. Read the record again before retrying.");
    }
}
