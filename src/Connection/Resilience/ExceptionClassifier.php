<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resilience;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\ResponseInterface;

final class ExceptionClassifier
{
    public function isRetryable(\Throwable $exception, RetryPolicy $policy): bool
    {
        $statusCode = $this->getStatusCode($exception);
        if ($statusCode !== null) {
            return in_array($statusCode, $policy->getRetryableStatusCodes(), true);
        }

        for ($current = $exception; $current !== null; $current = $current->getPrevious()) {
            if ($current instanceof NetworkExceptionInterface) {
                return true;
            }
        }

        return preg_match(
            '/timeout|timed out|temporar|connection (?:reset|refused)|could not resolve|network|broken pipe/i',
            $exception->getMessage(),
        ) === 1;
    }

    public function getStatusCode(\Throwable $exception): ?int
    {
        for ($current = $exception; $current !== null; $current = $current->getPrevious()) {
            if (method_exists($current, 'getStatusCode')) {
                $statusCode = $current->getStatusCode();
                if (is_int($statusCode) && $statusCode >= 100 && $statusCode <= 599) {
                    return $statusCode;
                }
            }

            $code = $current->getCode();
            if (is_int($code) && $code >= 100 && $code <= 599) {
                return $code;
            }
        }

        return null;
    }

    public function getRetryAfterMilliseconds(\Throwable $exception): ?int
    {
        for ($current = $exception; $current !== null; $current = $current->getPrevious()) {
            $response = null;
            if (method_exists($current, 'getResponse')) {
                $response = $current->getResponse();
            }
            if (!$response instanceof ResponseInterface) {
                continue;
            }

            $value = trim($response->getHeaderLine('Retry-After'));
            if ($value === '') {
                continue;
            }
            if (ctype_digit($value)) {
                return (int)$value * 1_000;
            }

            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return max(0, ($timestamp - time()) * 1_000);
            }
        }

        return null;
    }
}
