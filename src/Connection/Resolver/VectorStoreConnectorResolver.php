<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resolver;

use Madj2k\AiCore\Connection\VectorStore\VectorStoreConnectorInterface;
use Madj2k\AiCore\Exception\ConnectorNotFoundException;
use Madj2k\AiCore\Exception\DuplicateConnectorIdentifierException;

/**
 * Class VectorStoreConnectorResolver
 *
 * Resolves registered vector store connectors by their unique identifiers.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class VectorStoreConnectorResolver
{
    /** @var array<string, VectorStoreConnectorInterface> */
    private array $connectors = [];

    /**
     * @param iterable<VectorStoreConnectorInterface> $connectors Registered vector store connectors.
     * @throws \Madj2k\AiCore\Exception\DuplicateConnectorIdentifierException
     */
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

    /**
     * Returns the connector registered for an identifier.
     *
     * @throws \Madj2k\AiCore\Exception\ConnectorNotFoundException
     */
    public function get(string $identifier): VectorStoreConnectorInterface
    {
        return $this->connectors[$identifier]
            ?? throw new ConnectorNotFoundException('vector store', $identifier, 1780002001);
    }

    /** Determines whether an identifier is registered. */
    public function has(string $identifier): bool { return isset($this->connectors[$identifier]); }

    /**
     * Returns all connectors keyed by identifier.
     *
     * @return array<string, VectorStoreConnectorInterface>
     */
    public function all(): array { return $this->connectors; }
}
