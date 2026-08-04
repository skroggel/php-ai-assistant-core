<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Tests\Support;

use Madj2k\AiCore\Connection\Resilience\SleeperInterface;

final class RecordingSleeper implements SleeperInterface
{
    /** @var array<int, int> */
    public array $delays = [];

    public function sleep(int $milliseconds): void
    {
        $this->delays[] = $milliseconds;
    }
}
