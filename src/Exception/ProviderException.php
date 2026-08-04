<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Exception;

class ProviderException extends AppException
{
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

    public function getProvider(): string { return $this->provider; }
    public function getOperation(): string { return $this->operation; }
    public function getStatusCode(): ?int { return $this->statusCode; }
    public function isRetryable(): bool { return $this->retryable; }
    public function getAttempts(): int { return $this->attempts; }
}
