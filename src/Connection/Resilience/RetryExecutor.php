<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resilience;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class RetryExecutor
{
    /** @var \Closure(int): void */
    private \Closure $sleep;

    private ExceptionClassifier $exceptionClassifier;

    private LoggerInterface $logger;

    public function __construct(
        private RetryPolicy $policy,
        ?\Closure $sleep = null,
        ?LoggerInterface $logger = null,
    ) {
        $this->exceptionClassifier = new ExceptionClassifier();
        $this->logger = $logger ?? new NullLogger();
        $this->sleep = $sleep ?? static function (int $milliseconds): void {
            if ($milliseconds > 0) {
                usleep($milliseconds * 1_000);
            }
        };
    }

    /**
     * @template T
     * @param callable(): T $operation
     * @param (callable(\Throwable): bool)|null $shouldRetry
     * @return T
     */
    public function execute(
        string $provider,
        string $operationName,
        callable $operation,
        ?callable $shouldRetry = null,
    ): mixed {
        for ($attempt = 1; ; $attempt++) {
            try {
                return $operation();
            } catch (\Throwable $exception) {
                $retryable = $shouldRetry !== null
                    ? $shouldRetry($exception)
                    : $this->exceptionClassifier->isRetryable($exception, $this->policy);
                $statusCode = $this->exceptionClassifier->getStatusCode($exception);

                if (!$retryable || $attempt >= $this->policy->getMaxAttempts()) {
                    throw new RetryExhaustedException(
                        $provider,
                        $operationName,
                        $attempt,
                        $retryable,
                        $statusCode,
                        $exception,
                    );
                }

                $delay = $this->policy->getDelayMilliseconds($attempt);
                $this->logger->warning('Provider request will be retried', [
                    'provider' => $provider,
                    'operation' => $operationName,
                    'attempt' => $attempt,
                    'next_attempt' => $attempt + 1,
                    'delay_ms' => $delay,
                    'status_code' => $statusCode,
                    'exception' => $exception,
                ]);
                ($this->sleep)($delay);
            }
        }
    }
}
