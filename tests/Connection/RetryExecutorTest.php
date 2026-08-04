<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Tests\Connection;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Madj2k\AiCore\Connection\Resilience\RetryExecutor;
use Madj2k\AiCore\Connection\Resilience\RetryExhaustedException;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;
use Madj2k\AiCore\Tests\Support\RecordingSleeper;
use OpenAI\Exceptions\ErrorException;
use PHPUnit\Framework\TestCase;

final class RetryExecutorTest extends TestCase
{
    public function testRetriesRateLimitWithExponentialBackoff(): void
    {
        $sleeper = new RecordingSleeper();
        $executor = new RetryExecutor(
            new RetryPolicy(maxAttempts: 3, initialDelayMilliseconds: 10, backoffMultiplier: 2.0),
            $sleeper,
        );
        $attempts = 0;

        $result = $executor->execute('openai', 'chat', function () use (&$attempts): string {
            $attempts++;
            if ($attempts < 3) {
                throw new ErrorException(['message' => 'rate limited', 'type' => 'rate_limit', 'code' => 429], 429);
            }
            return 'ok';
        });

        self::assertSame('ok', $result);
        self::assertSame(3, $attempts);
        self::assertSame([10, 20], $sleeper->delays);
    }

    public function testDoesNotRetryNonRetryableProviderError(): void
    {
        $executor = new RetryExecutor(new RetryPolicy(maxAttempts: 3), new RecordingSleeper());

        try {
            $executor->execute('openai', 'chat', static function (): never {
                throw new ErrorException(['message' => 'bad request', 'type' => 'invalid_request', 'code' => 400], 400);
            });
            self::fail('Expected retry exception was not thrown.');
        } catch (RetryExhaustedException $exception) {
            self::assertSame(1, $exception->getAttempts());
            self::assertSame(400, $exception->getStatusCode());
            self::assertFalse($exception->isRetryable());
        }
    }

    public function testHonorsRetryAfterHeaderWithinConfiguredMaximum(): void
    {
        $sleeper = new RecordingSleeper();
        $executor = new RetryExecutor(
            new RetryPolicy(maxAttempts: 2, initialDelayMilliseconds: 10, maximumDelayMilliseconds: 2_000),
            $sleeper,
        );
        $attempts = 0;

        $result = $executor->execute('provider', 'operation', function () use (&$attempts): string {
            $attempts++;
            if ($attempts === 1) {
                $request = new Request('POST', 'https://provider.test');
                throw new RequestException(
                    'rate limited',
                    $request,
                    new Response(429, ['Retry-After' => '1']),
                );
            }
            return 'ok';
        });

        self::assertSame('ok', $result);
        self::assertSame([1_000], $sleeper->delays);
    }
}
