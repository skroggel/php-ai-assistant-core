<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Connection\Factory;

use GuzzleHttp\ClientInterface;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;

/**
 * Interface GeminiClientFactoryInterface
 *
 * Creates HTTP clients used to communicate with the Gemini API.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface GeminiClientFactoryInterface
{
    /**
     * Creates a Gemini HTTP client for the given connection and timeout policy.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface $connection AI connection.
     * @param \Madj2k\AiCore\Connection\Resilience\RetryPolicy $policy Retry and timeout policy.
     * @return \GuzzleHttp\ClientInterface Gemini HTTP client.
     */
    public function create(
        AiConnectionConfigurationInterface $connection,
        RetryPolicy $policy,
    ): ClientInterface;
}
