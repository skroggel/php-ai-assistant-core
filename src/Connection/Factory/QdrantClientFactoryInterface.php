<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Factory;

use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;
use Qdrant\Qdrant;

/**
 * Interface QdrantClientFactoryInterface
 *
 * Creates configured Qdrant SDK clients and provides an injection point for custom transports.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
interface QdrantClientFactoryInterface
{
    /**
     * Creates a Qdrant client for the given connection and timeout policy.
     */
    public function create(
        VectorStoreConnectionConfigurationInterface $connection,
        RetryPolicy $policy,
    ): Qdrant;
}
