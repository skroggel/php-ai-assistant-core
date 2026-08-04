<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resolver;

use Madj2k\AiCore\Connection\VectorStore\VectorStoreConnectorInterface;
use Madj2k\AiCore\Exception\ConnectorNotFoundException;
use Madj2k\AiCore\Exception\DuplicateConnectorIdentifierException;

final class VectorStoreConnectorResolver
{
    /** @var array<string, VectorStoreConnectorInterface> */
    private array $connectors = [];

    /** @param iterable<VectorStoreConnectorInterface> $connectors */
    public function __construct(iterable $connectors)
    {
        foreach ($connectors as $connector) {
            $identifier = $connector->getIdentifier();
            if (isset($this->connectors[$identifier])) {
                throw new DuplicateConnectorIdentifierException('vector store', $identifier);
            }
            $this->connectors[$identifier] = $connector;
        }
    }

    public function get(string $identifier): VectorStoreConnectorInterface
    {
        return $this->connectors[$identifier]
            ?? throw new ConnectorNotFoundException('vector store', $identifier, 1780002001);
    }

    public function has(string $identifier): bool { return isset($this->connectors[$identifier]); }

    /** @return array<string, VectorStoreConnectorInterface> */
    public function all(): array { return $this->connectors; }
}
