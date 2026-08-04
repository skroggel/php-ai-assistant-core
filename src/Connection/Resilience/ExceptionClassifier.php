<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resilience;

use Psr\Http\Client\NetworkExceptionInterface;

/**
 * Class ExceptionClassifier
 *
 * Extracts HTTP status codes and identifies transient provider failures eligible for retry.
 *
 * @internal Provider connectors expose normalized public exceptions instead.
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class ExceptionClassifier
{
    /**
     * Determines whether an exception represents a transient failure according to the policy.
     */
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

    /**
     * Returns the first valid HTTP status code found in the exception chain.
     */
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
}
