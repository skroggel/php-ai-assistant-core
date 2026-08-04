<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Factory;

use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;
use Qdrant\Qdrant;

interface QdrantClientFactoryInterface
{
    public function create(
        VectorStoreConnectionConfigurationInterface $connection,
        RetryPolicy $policy,
    ): Qdrant;
}
