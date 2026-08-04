<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Factory;

use GuzzleHttp\Client as HttpClient;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resilience\RetryPolicy;
use Qdrant\Config;
use Qdrant\Http\Builder;
use Qdrant\Qdrant;

final class QdrantClientFactory implements QdrantClientFactoryInterface
{
    public function create(
        VectorStoreConnectionConfigurationInterface $connection,
        RetryPolicy $policy,
    ): Qdrant {
        $config = new Config($connection->getEndpoint());
        if ($connection->getApiKey() !== '') {
            $config->setApiKey($connection->getApiKey());
        }

        $httpClient = new HttpClient([
            'timeout' => $policy->getTimeoutSeconds(),
            'connect_timeout' => $policy->getConnectTimeoutSeconds(),
        ]);

        return new Qdrant((new Builder($httpClient))->build($config));
    }
}
