<?php
declare(strict_types=1);

namespace Madj2k\AiCore\Connection\Resolver;

use Madj2k\AiCore\Connection\Ai\AiConnectorInterface;
use Madj2k\AiCore\Exception\ConnectorNotFoundException;
use Madj2k\AiCore\Exception\DuplicateConnectorIdentifierException;

/**
 * Class AiConnectorResolver
 *
 * Resolves registered AI connectors by their unique identifiers.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class AiConnectorResolver
{
    /** @var array<string, AiConnectorInterface> */
    private array $connectors = [];

    /**
     * @param iterable<AiConnectorInterface> $connectors Registered AI connectors.
     * @throws \Madj2k\AiCore\Exception\DuplicateConnectorIdentifierException
     */
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

    /**
     * Returns the connector registered for an identifier.
     *
     * @throws \Madj2k\AiCore\Exception\ConnectorNotFoundException
     */
    public function get(string $identifier): AiConnectorInterface
    {
        return $this->connectors[$identifier]
            ?? throw new ConnectorNotFoundException('AI', $identifier, 1780001001);
    }

    /** Determines whether an identifier is registered. */
    public function has(string $identifier): bool { return isset($this->connectors[$identifier]); }

    /**
     * Returns all connectors keyed by identifier.
     *
     * @return array<string, AiConnectorInterface>
     */
    public function all(): array { return $this->connectors; }
}
