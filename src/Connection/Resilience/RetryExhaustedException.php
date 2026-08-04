<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resilience;

/**
 * Class RetryExhaustedException
 *
 * Wraps the final provider failure together with retry diagnostics.
 *
 * @internal Provider connectors convert this exception into public provider exceptions.
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class RetryExhaustedException extends \RuntimeException
{
    /**
     * @param string $provider Provider identifier.
     * @param string $operation Operation identifier.
     * @param int $attempts Number of executed attempts.
     * @param bool $retryable Whether the final failure was classified as retryable.
     * @param int|null $statusCode Detected HTTP status code.
     * @param \Throwable $previous Original provider exception.
     */
    public function __construct(
        private readonly string $provider,
        private readonly string $operation,
        private readonly int $attempts,
        private readonly bool $retryable,
        private readonly ?int $statusCode,
        \Throwable $previous,
    ) {
        parent::__construct($previous->getMessage(), (int)$previous->getCode(), $previous);
    }

    /** Returns the provider identifier. */
    public function getProvider(): string { return $this->provider; }

    /** Returns the operation identifier. */
    public function getOperation(): string { return $this->operation; }

    /** Returns the number of executed attempts. */
    public function getAttempts(): int { return $this->attempts; }

    /** Determines whether the final failure was classified as retryable. */
    public function isRetryable(): bool { return $this->retryable; }

    /** Returns the detected HTTP status code. */
    public function getStatusCode(): ?int { return $this->statusCode; }
}
