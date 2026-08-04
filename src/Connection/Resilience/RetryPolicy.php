<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resilience;

final readonly class RetryPolicy
{
    /** @param array<int, int> $retryableStatusCodes */
    public function __construct(
        private int $maxAttempts = 3,
        private int $initialDelayMilliseconds = 250,
        private float $backoffMultiplier = 2.0,
        private int $maximumDelayMilliseconds = 2_000,
        private float $timeoutSeconds = 30.0,
        private float $connectTimeoutSeconds = 10.0,
        private array $retryableStatusCodes = [408, 409, 425, 429, 500, 502, 503, 504],
    ) {
        if ($maxAttempts < 1) {
            throw new \InvalidArgumentException('Maximum attempts must be at least one.');
        }
        if ($initialDelayMilliseconds < 0 || $maximumDelayMilliseconds < 0) {
            throw new \InvalidArgumentException('Retry delays must not be negative.');
        }
        if ($backoffMultiplier < 1.0) {
            throw new \InvalidArgumentException('Backoff multiplier must be at least one.');
        }
        if ($timeoutSeconds <= 0.0 || $connectTimeoutSeconds <= 0.0) {
            throw new \InvalidArgumentException('Timeouts must be greater than zero.');
        }
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getTimeoutSeconds(): float
    {
        return $this->timeoutSeconds;
    }

    public function getConnectTimeoutSeconds(): float
    {
        return $this->connectTimeoutSeconds;
    }

    /** @return array<int, int> */
    public function getRetryableStatusCodes(): array
    {
        return $this->retryableStatusCodes;
    }

    public function getDelayMilliseconds(int $failedAttempt): int
    {
        $delay = (int)round(
            $this->initialDelayMilliseconds * ($this->backoffMultiplier ** max(0, $failedAttempt - 1))
        );

        return min($delay, $this->maximumDelayMilliseconds);
    }
}
