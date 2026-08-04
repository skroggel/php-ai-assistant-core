<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resilience;

interface SleeperInterface
{
    public function sleep(int $milliseconds): void;
}
