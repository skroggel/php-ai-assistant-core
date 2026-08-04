<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Health;

use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;
use Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection;

final readonly class ConnectionHealthChecker
{
    public function __construct(
        private AiConnectorResolver $aiConnectorResolver,
        private VectorStoreConnectorResolver $vectorStoreConnectorResolver,
    ) {
    }

    public function checkAi(
        AiConnectionConfigurationInterface $connection,
        string $probeText = 'AI connection test',
    ): bool {
        $response = $this->aiConnectorResolver
            ->get($connection->getConnectorIdentifier())
            ->embed($connection, new EmbeddingRequest($probeText));

        return $response->getEmbedding() !== [];
    }

    public function checkVectorStore(
        VectorStoreConnectionConfigurationInterface $connection,
        ?VectorCollection $collection = null,
    ): bool {
        $collection ??= new VectorCollection(
            $connection->getDefaultCollection() !== ''
                ? $connection->getDefaultCollection()
                : '_connection_test',
            $connection->getVectorSize(),
            $connection->getDistance(),
        );

        return $this->vectorStoreConnectorResolver
            ->get($connection->getConnectorIdentifier())
            ->ensureCollection($connection, $collection);
    }
}
