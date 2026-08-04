<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Health;

use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingRequest;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;
use Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection;

/**
 * Class ConnectionHealthChecker
 *
 * Performs minimal provider operations to verify AI and vector store connections.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class ConnectionHealthChecker
{
    /**
     * @param \Madj2k\AiCore\Connection\Resolver\AiConnectorResolver $aiConnectorResolver AI connector resolver.
     * @param \Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver $vectorStoreConnectorResolver Vector store connector resolver.
     */
    public function __construct(
        private AiConnectorResolver $aiConnectorResolver,
        private VectorStoreConnectorResolver $vectorStoreConnectorResolver,
    ) {
    }

    /**
     * Verifies an AI connection by requesting an embedding for a probe text.
     *
     * @throws \Throwable When connector resolution or the provider request fails.
     */
    public function checkAi(
        AiConnectionConfigurationInterface $connection,
        string $probeText = 'AI connection test',
    ): bool {
        $response = $this->aiConnectorResolver
            ->get($connection->getConnectorIdentifier())
            ->embed($connection, new EmbeddingRequest($probeText));

        return $response->getEmbedding() !== [];
    }

    /**
     * Verifies a vector store connection by ensuring a collection exists.
     *
     * If no collection is supplied, the configured default or a dedicated probe collection is used.
     *
     * @throws \Throwable When connector resolution or the provider request fails.
     */
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
