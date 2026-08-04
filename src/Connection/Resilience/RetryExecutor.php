<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resilience;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class RetryExecutor
 *
 * Executes provider operations with bounded retry delays and structured retry logging.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class RetryExecutor
{
    /** @var \Closure(int): void */
    private \Closure $sleep;

    private ExceptionClassifier $exceptionClassifier;

    private LoggerInterface $logger;

    /**
     * @param \Madj2k\AiCore\Connection\Resilience\RetryPolicy $policy Retry and timeout policy.
     * @param (\Closure(int): void)|null $sleep Optional delay callback receiving milliseconds.
     * @param \Psr\Log\LoggerInterface|null $logger Retry logger.
     */
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
     * Executes an operation until it succeeds or retry handling is exhausted.
     *
     * @template T
     * @param string $provider Provider identifier used for diagnostics.
     * @param string $operationName Operation identifier used for diagnostics.
     * @param callable(): T $operation
     * @param (callable(\Throwable): bool)|null $shouldRetry Optional operation-specific classifier.
     * @return T
     * @throws \Madj2k\AiCore\Connection\Resilience\RetryExhaustedException
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
