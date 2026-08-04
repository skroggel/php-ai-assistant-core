<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Exception;

/**
 * Class ProviderException
 *
 * Base exception carrying normalized diagnostics for external provider failures.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
class ProviderException extends AppException
{
    /**
     * @param string $message Exception message.
     * @param int $code Application-specific exception code.
     * @param \Throwable|null $previous Original provider exception.
     * @param string $provider Provider identifier.
     * @param string $operation Operation identifier.
     * @param int|null $statusCode Detected HTTP status code.
     * @param bool $retryable Whether the final failure was classified as retryable.
     * @param int $attempts Number of executed attempts.
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        private readonly string $provider = '',
        private readonly string $operation = '',
        private readonly ?int $statusCode = null,
        private readonly bool $retryable = false,
        private readonly int $attempts = 1,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /** Returns the provider identifier. */
    public function getProvider(): string { return $this->provider; }

    /** Returns the operation identifier. */
    public function getOperation(): string { return $this->operation; }

    /** Returns the detected HTTP status code. */
    public function getStatusCode(): ?int { return $this->statusCode; }

    /** Determines whether the final failure was classified as retryable. */
    public function isRetryable(): bool { return $this->retryable; }

    /** Returns the number of executed attempts. */
    public function getAttempts(): int { return $this->attempts; }
}
