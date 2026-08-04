<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Factory;

use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;
use OpenAI\Contracts\ClientContract;

/**
 * Interface OpenAiClientFactoryInterface
 *
 * Creates configured OpenAI SDK clients and provides an injection point for custom transports.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface OpenAiClientFactoryInterface
{
    /**
     * Creates an OpenAI client for the given connection and timeout policy.
     */
    public function create(
        AiConnectionConfigurationInterface $connection,
        RetryPolicy $policy,
    ): ClientContract;
}
