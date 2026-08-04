<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resolver;

use Madj2k\AiCore\Connection\Ai\AiConnectorInterface;
use Madj2k\AiCore\Exception\ConnectorNotFoundException;
use Madj2k\AiCore\Exception\DuplicateConnectorIdentifierException;

final class AiConnectorResolver
{
    /** @var array<string, AiConnectorInterface> */
    private array $connectors = [];

    /** @param iterable<AiConnectorInterface> $connectors */
    public function __construct(iterable $connectors)
    {
        foreach ($connectors as $connector) {
            $identifier = $connector->getIdentifier();
            if (isset($this->connectors[$identifier])) {
                throw new DuplicateConnectorIdentifierException('AI', $identifier);
            }
            $this->connectors[$identifier] = $connector;
        }
    }

    public function get(string $identifier): AiConnectorInterface
    {
        return $this->connectors[$identifier]
            ?? throw new ConnectorNotFoundException('AI', $identifier, 1780001001);
    }

    public function has(string $identifier): bool { return isset($this->connectors[$identifier]); }

    /** @return array<string, AiConnectorInterface> */
    public function all(): array { return $this->connectors; }
}
