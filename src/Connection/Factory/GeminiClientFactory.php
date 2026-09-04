<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Connection\Factory;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;

/**
 * Class GeminiClientFactory
 *
 * Creates timeout-aware HTTP clients for Gemini API requests.
 *
 * @internal Use GeminiClientFactoryInterface to provide custom client creation.
 * @author Maximilian Fäßer <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class GeminiClientFactory implements GeminiClientFactoryInterface
{
    /** @inheritDoc */
    public function create(
        AiConnectionConfigurationInterface $connection,
        RetryPolicy $policy,
    ): ClientInterface {
        return new Client([
            'timeout' => $policy->getTimeoutSeconds(),
            'connect_timeout' => $policy->getConnectTimeoutSeconds(),
        ]);
    }
}
