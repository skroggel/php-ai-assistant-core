<?php
declare(strict_types=1);

/*
 * This file is part of madj2k/ai-core.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiCore\Indexing\Resolver;

use Madj2k\AiCore\Exception\IndexingException;
use Madj2k\AiCore\Indexing\Connector\ConnectorInterface;

/**
 * Class IndexingConnectorResolver
 *
 * Resolves tagged indexing connector services by identifier.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiCore
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class IndexingConnectorResolver
{
    /**
     * Connectors.
     *
     * @var iterable<\Madj2k\AiCore\Indexing\Connector\ConnectorInterface>
     */
    protected iterable $connectors;


    /**
     * Constructor.
     *
     * @param iterable<\Madj2k\AiCore\Indexing\Connector\ConnectorInterface> $connectors Connectors.
     */
    public function __construct(iterable $connectors)
    {
        $this->connectors = $connectors;
    }


    /**
     * Returns a connector by identifier.
     *
     * @param string $identifier Connector identifier.
     * @return \Madj2k\AiCore\Indexing\Connector\ConnectorInterface Connector.
     * @throws \Madj2k\AiCore\Exception\IndexingException
     */
    public function get(string $identifier): ConnectorInterface
    {
        foreach ($this->connectors as $connector) {
            if ($connector->getIdentifier() === $identifier) {
                return $connector;
            }
        }

        throw new IndexingException(sprintf('No indexing connector registered for identifier "%s".', $identifier), 1760001101);
    }


    /**
     * Returns all registered connectors.
     *
     * @return array<int, \Madj2k\AiCore\Indexing\Connector\ConnectorInterface> Connectors.
     */
    public function all(): array
    {
        return is_array($this->connectors)
            ? $this->connectors
            : iterator_to_array($this->connectors);
    }


    /**
     * Returns registered identifiers.
     *
     * @return array<int, string> Identifiers.
     */
    public function getIdentifiers(): array
    {
        return array_map(
            static fn (ConnectorInterface $connector): string => $connector->getIdentifier(),
            $this->all()
        );
    }
}
