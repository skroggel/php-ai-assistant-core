<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resilience;

/**
 * Class RetryPolicy
 *
 * Defines bounded exponential backoff and transport timeouts for provider requests.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class RetryPolicy
{
    /**
     * @param int $maxAttempts Maximum number of attempts including the initial request.
     * @param int $initialDelayMilliseconds Delay after the first failed attempt.
     * @param float $backoffMultiplier Multiplier applied after each failed attempt.
     * @param int $maximumDelayMilliseconds Upper delay bound.
     * @param float $timeoutSeconds Overall request timeout in seconds.
     * @param float $connectTimeoutSeconds Connection timeout in seconds.
     * @param array<int, int> $retryableStatusCodes HTTP status codes eligible for retry.
     */
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

    /** Returns the maximum number of attempts including the initial request. */
    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /** Returns the overall request timeout in seconds. */
    public function getTimeoutSeconds(): float
    {
        return $this->timeoutSeconds;
    }

    /** Returns the connection timeout in seconds. */
    public function getConnectTimeoutSeconds(): float
    {
        return $this->connectTimeoutSeconds;
    }

    /**
     * Returns HTTP status codes eligible for retry.
     *
     * @return array<int, int>
     */
    public function getRetryableStatusCodes(): array
    {
        return $this->retryableStatusCodes;
    }

    /**
     * Calculates the bounded delay after a failed attempt.
     */
    public function getDelayMilliseconds(int $failedAttempt): int
    {
        $delay = (int)round(
            $this->initialDelayMilliseconds * ($this->backoffMultiplier ** max(0, $failedAttempt - 1))
        );

        return min($delay, $this->maximumDelayMilliseconds);
    }
}
