<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resilience;

final class NativeSleeper implements SleeperInterface
{
    public function sleep(int $milliseconds): void
    {
        if ($milliseconds > 0) {
            usleep($milliseconds * 1_000);
        }
    }
}
