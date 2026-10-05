<?php

namespace Tests\Fixtures\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

class FailingTestJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public string $reason = 'Simulated job failure for testing') {}

    public function handle(): void
    {
        throw new RuntimeException($this->reason);
    }
}
