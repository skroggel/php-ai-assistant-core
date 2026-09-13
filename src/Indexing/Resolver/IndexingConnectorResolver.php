<?php
declare(strict_types=1);

/*
 * This file is part of madj2k\ai-core
 *
 * Copyright (C) 2026 Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
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
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiCore
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
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
