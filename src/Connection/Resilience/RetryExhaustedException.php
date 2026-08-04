<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resilience;

final class RetryExhaustedException extends \RuntimeException
{
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

    public function getProvider(): string { return $this->provider; }
    public function getOperation(): string { return $this->operation; }
    public function getAttempts(): int { return $this->attempts; }
    public function isRetryable(): bool { return $this->retryable; }
    public function getStatusCode(): ?int { return $this->statusCode; }
}
